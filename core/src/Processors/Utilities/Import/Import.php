<?php

namespace ModxPro\MiniShop3\Processors\Utilities\Import;

use ModxPro\MiniShop3\Utils\ImportCSV;
use MODX\Revolution\Processors\ModelProcessor;
use ModxPro\MiniShop3\MiniShop3;
use ModxPro\MiniShop3\Model\msProduct;

class Import extends ModelProcessor
{

    public $classKey = msProduct::class;
    public $objectType = 'msProduct';
    public $languageTopics = ['minishop3:default', 'minishop3:manager'];
    public $permission = 'msproduct_save';
    public $properties = [];

    /** @var MiniShop3 $ms3 */
    protected $ms3;

    /**
     * @return bool|null|string
     */
    public function initialize()
    {
        $this->properties = $this->getProperties();

        return parent::initialize();
    }

    /**
     * {@inheritDoc}
     */
    public function process()
    {
        $required = ['importfile', 'fields', 'delimiter'];

        foreach ($required as $field) {
            if (!trim($this->getProperty($field))) {
                return $this->addFieldError($field, $this->modx->lexicon('field_required'));
            }
        }

        $importParams = [
            'file' => $this->properties['importfile'],
            'fields' => $this->properties['fields'],
            'update' => $this->properties['update'],
            'key' => $this->properties['key'],
            'debug' => $this->properties['debug'],
            'delimiter' => $this->properties['delimiter'],
            'skip_header' => $this->properties['skip_header'],
        ];

        $useScheduler = $this->getProperty('scheduler', 0);
        if (empty($useScheduler)) {
            $importCSV = new ImportCSV($this->modx);
            return $importCSV->process($importParams);
        }

        $schedulerPath = $this->modx->getOption(
            'scheduler.core_path',
            null,
            $this->modx->getOption('core_path') . 'components/scheduler/'
        );
        if(!file_exists($schedulerPath . 'model/scheduler/scheduler.class.php')) {
            return $this->failure($this->modx->lexicon('ms3_utilities_scheduler_nf'));
        }
        require_once $schedulerPath . 'model/scheduler/scheduler.class.php';
        $scheduler = new \Scheduler($this->modx);
        $task = $scheduler->getTask('MiniShop3', 'ms3_csv_import');
        if (!$task) {
            $task = $this->createImportTask();
        }
        if (empty($task)) {
            return $this->failure($this->modx->lexicon('ms3_utilities_scheduler_task_ce'));
        }

        $task->schedule('+1 second', $importParams);

        return $this->success($this->modx->lexicon('ms3_utilities_scheduler_success'));
    }

    /**
     * Creating Sheduler's task for start import
     * @return false|object|null
     */
    private function createImportTask()
    {
        $task = $this->modx->newObject('sFileTask');
        $task->fromArray([
            'class_key' => 'sFileTask',
            'content' => '/tasks/csvImport.php',
            'namespace' => 'MiniShop3',
            'reference' => 'ms3_csv_import',
            'description' => 'MiniShop3 CSV import'
        ]);
        if (!$task->save()) {
            return false;
        }
        return $task;
    }
}
