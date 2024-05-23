<?php

namespace ModxPro\MiniShop3\Console\Command;

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
use ModxPro\MiniShop3\MiniShop3;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use xPDOTransport;

class Remove extends Install
{
    protected static $defaultName = 'remove';
    protected static $defaultDescription = 'Remove MiniShop3 from MODX';

    public function run(InputInterface $input, OutputInterface $output): void
    {
        $this->output = $output;

        $this->processElements();
        $this->processCategory();
        $this->processNamespace();
        $this->processSymlinks();
        $this->processResolvers();
        $this->clearCache();
    }

    protected function processSymlinks(): void
    {
        if (is_dir($this->corePath)) {
            unlink($this->corePath);
            $this->log('Removed symlink for "core"');
        }
        if (is_dir($this->assetsPath)) {
            unlink($this->assetsPath);
            $this->log('Removed symlink for "assets"');
        }
    }

    protected function processNamespace(): void
    {
        if ($this->namespace = $this->modx->getObject(modNamespace::class, ['name' => MiniShop3::NAMESPACE])) {
            $this->namespace->remove();
            $this->log('Removed namespace');
        }
    }

    protected function processCategory(): void
    {
        if ($this->category = $this->modx->getObject(modCategory::class, ['category' => MiniShop3::NAME])) {
            $this->category->remove();
            $this->log('Removed category');
        }
    }

    protected function processChunks(array $data): void
    {
        foreach (array_keys($data) as $name) {
            if ($chunk = $this->modx->getObject(modChunk::class, ['name' => $name])) {
                $chunk->remove();
                $this->log('Removed chunk "' . $chunk->name . '"');
            }
        }
    }

    protected function processEvents(array $data): void
    {
        foreach ($data as $name) {
            if ($event = $this->modx->getObject(modEvent::class, ['name' => $name])) {
                $event->remove();
                $this->log('Removed event "' . $event->name . '"');
            }
        }
    }

    protected function processMenus(array $data): void
    {
        foreach (array_keys($data) as $name) {
            if ($menu = $this->modx->getObject(modMenu::class, ['text' => $name])) {
                $menu->remove();
                $this->log('Removed menu "' . $menu->text . '"');
            }
        }
    }

    protected function processPlugins(array $data): void
    {
        foreach (array_keys($data) as $name) {
            if ($plugin = $this->modx->getObject(modPlugin::class, ['name' => $name])) {
                $plugin->remove();

                /** @var modPluginEvent $event */
                foreach ($plugin->getMany('Events') as $event) {
                    $this->log('Removed event "' . $event->event . '" from plugin "' . $plugin->name . '"');
                    $event->remove();
                }
                $this->log('Removed plugin "' . $plugin->name . '"');
            }
        }
    }

    public function processPolicies(array $data): void
    {
        foreach (array_keys($data) as $name) {
            if ($policy = $this->modx->getObject(modAccessPolicy::class, ['name' => $name])) {
                $policy->remove();
                $this->log('Removed access policy "' . $policy->name . '"');
            }
        }
    }

    public function processPolicyTemplates(array $data): void
    {
        foreach (array_keys($data) as $name) {
            if ($template = $this->modx->getObject(modAccessPolicyTemplate::class, ['name' => $name])) {
                $template->remove();
                $this->log('Removed access policy template "' . $template->name . '"');
            }
        }
    }

    protected function processSettings(array $data): void
    {
        foreach (array_keys($data) as $key) {
            if ($setting = $this->modx->getObject(modSystemSetting::class, ['key' => $key])) {
                $setting->remove();
                $this->log('Removed system setting "' . $setting->key . '"');
            }
        }
    }

    protected function processSnippets(array $data): void
    {
        foreach (array_keys($data) as $name) {
            if ($snippet = $this->modx->getObject(modSnippet::class, ['name' => $name])) {
                $snippet->remove();
                $this->log('Removed snippet "' . $snippet->name . '"');
            }
        }
    }

    protected function processResolvers(): void
    {
        $this->log('Processing package resolvers...');

        $resolvers = glob($this->buildPath . '/resolvers/*.php');
        $transport = new xPDOTransport($this->modx, MiniShop3::NAMESPACE, '');
        $options = [xPDOTransport::PACKAGE_ACTION => xPDOTransport::ACTION_UNINSTALL];

        foreach ($resolvers as $file) {
            require $file;
        }
    }
}
