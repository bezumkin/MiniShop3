<?php

namespace ModxPro\MiniShop3\Console\Command;

use MODX\Revolution\modAccessPermission;
use MODX\Revolution\modAccessPolicy;
use MODX\Revolution\modAccessPolicyTemplate;
use MODX\Revolution\modCategory;
use MODX\Revolution\modChunk;
use MODX\Revolution\modEvent;
use MODX\Revolution\modMenu;
use MODX\Revolution\modNamespace;
use MODX\Revolution\modPlugin;
use MODX\Revolution\modPluginEvent;
use MODX\Revolution\modSnippet;
use MODX\Revolution\modSystemSetting;
use MODX\Revolution\modX;
use ModxPro\MiniShop3\MiniShop3;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use xPDOTransport;

class Install extends Command
{
    protected static $defaultName = 'install';
    protected static $defaultDescription = 'Install MiniShop3 for MODX';
    protected modX $modx;
    protected ?OutputInterface $output;

    protected string $srcPath;
    protected string $corePath;
    protected string $assetsPath;
    protected string $buildPath;
    protected string $elementsPath;
    protected array $elementsConfig = [];

    protected ?modNamespace $namespace;
    protected ?modCategory $category;

    public function __construct(modX $modx, ?string $name = null)
    {
        parent::__construct($name);
        $this->modx = $modx;
        $this->srcPath = MODX_CORE_PATH . 'vendor/' . MiniShop3::PACKAGE;
        $this->corePath = MODX_CORE_PATH . 'components/' . MiniShop3::NAMESPACE;
        $this->assetsPath = MODX_ASSETS_PATH . 'components/' . MiniShop3::NAMESPACE;
        $this->buildPath = $this->corePath . '/_build';
        $this->elementsPath = $this->corePath . '/elements';
    }

    public function run(InputInterface $input, OutputInterface $output): void
    {
        $this->output = $output;

        $this->processSymlinks();
        $this->processNamespace();
        $this->processCategory();
        $this->processElements();
        $this->processResolvers(); // Should be replaced with migrations and seeding
        $this->clearCache();
    }

    protected function processSymlinks(): void
    {
        if (!is_dir($this->corePath)) {
            symlink($this->srcPath . '/core', $this->corePath);
            $this->log('Created symlink for "core"');
        }
        if (!is_dir($this->assetsPath)) {
            symlink($this->srcPath . '/assets/dist', $this->assetsPath);
            $this->log('Created symlink for "assets"');
        }
    }

    protected function processNamespace(): void
    {
        if (!$namespace = $this->modx->getObject(modNamespace::class, ['name' => MiniShop3::NAMESPACE])) {
            $namespace = new modNamespace($this->modx);
            $namespace->name = MiniShop3::NAMESPACE;
            $namespace->path = str_replace(MODX_CORE_PATH, '{core_path}', $this->corePath) . '/';
            $namespace->assets_path = str_replace(MODX_ASSETS_PATH, '{assets_path}', $this->assetsPath) . '/';
            $namespace->save();
            $this->log('Created namespace "' . $namespace->name . '"');
        }
        $this->namespace = $namespace;
    }

    protected function processCategory(): void
    {
        if (!$category = $this->modx->getObject(modCategory::class, ['category' => MiniShop3::NAME])) {
            $category = new modCategory($this->modx);
            $category->category = MiniShop3::NAME;
            $category->save();
            $this->log('Created category "' . $category->category . '"');
        }
        $this->category = $category;
    }

    protected function processElements(): void
    {
        $this->elementsConfig = require $this->buildPath . '/config.inc.php';
        $elements = glob($this->buildPath . '/elements/*.php');
        foreach ($elements as $file) {
            $data = require $file;
            $filename = pathinfo(basename($file), PATHINFO_FILENAME);
            $this->log("Processing $filename...");

            $method = 'process' . implode('', array_map('ucfirst', explode('_', $filename)));
            if (!method_exists($this, $method)) {
                $this->log('Could not find "' . $method . '()" method', 'error');
                continue;
            }
            $this->$method($data);
        }
    }

    protected function processChunks(array $data): void
    {
        $update = !empty($this->elementsConfig['update']['chunks']);
        $static = !empty($this->elementsConfig['static']['chunks']);

        foreach ($data as $name => $basename) {
            $file = $this->elementsPath . '/chunks/' . $basename . '.tpl';
            if (!file_exists($file)) {
                $this->log('Could not find "' . $file . '"', 'error');
                continue;
            }

            $new = false;
            /** @var modChunk $chunk */
            if (!$chunk = $this->modx->getObject(modChunk::class, ['name' => $name])) {
                $chunk = new modChunk($this->modx);
                $chunk->name = $name;
                $chunk->description = '';
                $new = true;
            }
            if ($new || $update) {
                $chunk->category = $this->category->id;
                $chunk->content = file_get_contents($file);
                $chunk->source = 1;
                $chunk->static_file = str_replace(MODX_CORE_PATH, '', $file);
                if ($static) {
                    $chunk->static = true;
                }
                $chunk->save();
                $this->log(($new ? 'Created' : 'Updated') . ' chunk "' . $chunk->name . '"');
            }
        }
    }

    protected function processEvents(array $data): void
    {
        foreach ($data as $name) {
            if ($this->modx->getCount(modEvent::class, ['name' => $name])) {
                continue;
            }
            $element = new modEvent($this->modx);
            $element->name = $name;
            $element->service = 6;
            $element->groupname = MiniShop3::NAME;
            $element->save();
            $this->log('Created event "' . $name . '"');
        }
    }

    protected function processMenus(array $data): void
    {
        $update = !empty($this->elementsConfig['menus']['chunks']);

        foreach ($data as $name => $properties) {
            $new = false;
            /** @var modMenu $menu */
            if (!$menu = $this->modx->getObject(modMenu::class, ['text' => $name])) {
                $menu = new modMenu($this->modx);
                $menu->text = $name;
                $new = true;
            }
            if ($new || $update) {
                $menu->fromArray(array_merge([
                    'parent' => 'components',
                    'namespace' => MiniShop3::NAMESPACE,
                    'icon' => '',
                    'menuindex' => 0,
                    'params' => '',
                    'handler' => '',
                ], $properties), '', true, true);
                $menu->save();
                $this->log(($new ? 'Created' : 'Updated') . ' menu "' . $menu->text . '"');
            }
        }
    }

    protected function processPlugins(array $data): void
    {
        $update = !empty($this->elementsConfig['update']['plugins']);
        $static = !empty($this->elementsConfig['static']['plugins']);

        foreach ($data as $name => $properties) {
            $file = $this->elementsPath . '/plugins/' . $properties['file'] . '.php';
            if (!file_exists($file)) {
                $this->log('Could not find "' . $file . '"', 'error');
                continue;
            }

            $new = false;
            /** @var modPlugin $plugin */
            if (!$plugin = $this->modx->getObject(modPlugin::class, ['name' => $name])) {
                $plugin = new modPlugin($this->modx);
                $plugin->name = $name;
                $plugin->description = '';
                $new = true;
            }
            if ($new || $update) {
                $plugin->fromArray($properties);
                $plugin->category = $this->category->id;
                $plugin->plugincode = preg_replace('#^<\?php#', '', file_get_contents($file));
                $plugin->source = 1;
                $plugin->static_file = str_replace(MODX_CORE_PATH, '', $file);
                if ($static) {
                    $plugin->static = true;
                }
                $plugin->save();
                $this->log(($new ? 'Created' : 'Updated') . ' plugin "' . $plugin->name . '"');
            }

            foreach ($properties['events'] as $eventName) {
                $key = ['pluginid' => $plugin->id, 'event' => $eventName];
                /** @var modEvent $event */
                if (!$this->modx->getObject(modPluginEvent::class, $key)) {
                    $event = new modPluginEvent($this->modx);
                    $event->fromArray($key, '', true, true);
                    $event->save();
                    $this->log('Added event "' . $event->event . '" to plugin "' . $plugin->name . '"');
                }
            }

            $condition = $this->modx->newQuery(modPluginEvent::class);
            $condition->where(['pluginid' => $plugin->id, 'event:NOT IN' => $properties['events']]);
            $iterator = $this->modx->getIterator(modPluginEvent::class, $condition);
            foreach ($iterator as $event) {
                /** @var modPluginEvent $event */
                $event->remove();
                $this->log('Removed event "' . $event->event . '" from plugin "' . $plugin->name . '"');
            }
        }
    }

    public function processPolicies(array $data): void
    {
        $update = !empty($this->elementsConfig['update']['policies']);

        foreach ($data as $name => $properties) {
            $new = false;
            if (!$policy = $this->modx->getObject(modAccessPolicy::class, ['name' => $name])) {
                $policy = new modAccessPolicy($this->modx);
                $policy->name = $name;
                $new = true;
            }

            if ($new || $update) {
                $policy->fromArray(array_merge([
                    'lexicon' => MiniShop3::NAMESPACE . ':permissions',
                ]), '', true, true);
                $policy->save();

                $this->log(($new ? 'Created' : 'Updated') . ' access policy "' . $policy->name . '"');
            }
        }
    }

    public function processPolicyTemplates(array $data): void
    {
        $update = !empty($this->elementsConfig['update']['policy_templates']);

        foreach ($data as $name => $properties) {
            $new = false;
            if (!$template = $this->modx->getObject(modAccessPolicyTemplate::class, ['name' => $name])) {
                $template = new modAccessPolicyTemplate($this->modx);
                $template->name = $name;
                $new = true;
            }

            if ($new || $update) {
                $template->fromArray($properties, '', true, true);
                $template->save();

                $this->log(($new ? 'Created' : 'Updated') . ' access policy template "' . $template->name . '"');
                if (!isset($properties['permissions']) || !is_array($properties['permissions'])) {
                    continue;
                }

                foreach ($properties['permissions'] as $permissionName => $permissionProperties) {
                    $key = ['template' => $template->id, 'name' => $permissionName];
                    if (!$this->modx->getCount(modAccessPermission::class, $key)) {
                        $permission = new modAccessPermission($this->modx);
                        $permission->template = $template->id;
                        $permission->fromArray(array_merge([
                            'name' => $permissionName,
                            'description' => $permissionName,
                            'value' => true,
                        ]), '', true, true);
                        $permission->save();

                        $this->log('Created access permission "' . $permission->name . '"');
                    }
                }
            }
        }
    }

    protected function processSettings(array $data): void
    {
        $update = !empty($this->elementsConfig['update']['settings']);

        $new = false;
        foreach ($data as $key => $properties) {
            if (!$setting = $this->modx->getObject(modSystemSetting::class, ['key' => $key])) {
                $setting = new modSystemSetting($this->modx);
                $setting->key = $key;
                $new = true;
            }

            if ($new || $update) {
                $setting->fromArray(array_merge([
                    'namespace' => $this->namespace?->name,
                ], $properties), '', true, true);
                $setting->save();
                $this->log(($new ? 'Created' : 'Updated') . ' system setting "' . $setting->key . '"');
            }
        }
    }

    protected function processSnippets(array $data): void
    {
        $update = !empty($this->elementsConfig['update']['snippets']);
        $static = !empty($this->elementsConfig['static']['snippets']);

        foreach ($data as $name => $properties) {
            $file = $this->elementsPath . '/snippets/' . $properties['file'] . '.php';
            if (!file_exists($file)) {
                $this->log('Could not find "' . $file . '"', 'error');
                continue;
            }

            $new = false;
            /** @var modSnippet $snippet */
            if (!$snippet = $this->modx->getObject(modSnippet::class, ['name' => $name])) {
                $snippet = new modSnippet($this->modx);
                $snippet->name = $name;
                $snippet->description = '';
                $new = true;
            }
            if ($new || $update) {
                $snippet->fromArray($properties);
                $snippet->category = $this->category->id;
                $snippet->snippet = preg_replace('#^<\?php#', '', file_get_contents($file));
                $snippet->source = 1;
                $snippet->static_file = str_replace(MODX_CORE_PATH, '', $file);
                if ($static) {
                    $snippet->static = true;
                }
                $snippetProperties = [];
                if (isset($properties['properties']) && is_array($properties['properties'])) {
                    foreach ($properties['properties'] as $propertyName => $propertyData) {
                        $snippetProperties[$propertyName] = array_merge([
                            'name' => $propertyName,
                            'desc' => 'ms_prop_' . $propertyName,
                            'lexicon' => 'minishop3:properties',
                        ], $propertyData);
                    }
                }
                $snippet->properties = $snippetProperties;
                $snippet->save();
                $this->log(($new ? 'Created' : 'Updated') . ' snippet "' . $snippet->name . '"');
            }
        }
    }

    protected function processResolvers(): void
    {
        $this->log('Processing package resolvers...');

        $resolvers = glob($this->buildPath . '/resolvers/*.php');
        $transport = new xPDOTransport($this->modx, MiniShop3::NAMESPACE, '');
        $options = [xPDOTransport::PACKAGE_ACTION => xPDOTransport::ACTION_INSTALL];

        foreach ($resolvers as $file) {
            require $file;
        }
    }

    protected function log(string $message, string $level = 'info'): void
    {
        $this->output?->writeln("<$level>$message</$level>");
    }

    protected function clearCache(): void
    {
        $this->modx->getCacheManager()->refresh();
        $this->log('Cleared MODX cache');
    }
}
