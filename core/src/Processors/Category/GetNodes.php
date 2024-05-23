<?php

namespace ModxPro\MiniShop3\Processors\Category;

use ModxPro\MiniShop3\Model\msCategoryMember;
use MODX\Revolution\modContext;
use MODX\Revolution\modResource;
use MODX\Revolution\Processors\Resource\GetNodes as ParentProcessor;
use xPDO\Om\xPDOQuery;

class GetNodes extends ParentProcessor
{
    protected int $resource_id = 0;
    protected int $parent_id = 0;

    /**
     * @return bool
     */
    public function initialize()
    {
        $initialize = parent::initialize();

        $this->parent_id = $this->getProperty('parent', 0);
        $this->resource_id = $this->getProperty('resource', 0);

        return $initialize;
    }

    /**
     * @return xPDOQuery
     */
    public function getResourceQuery()
    {
        $resourceColumns = [
            'id',
            'pagetitle',
            'longtitle',
            'alias',
            'description',
            'parent',
            'published',
            'deleted',
            'isfolder',
            'menuindex',
            'menutitle',
            'hidemenu',
            'class_key',
            'context_key',
        ];
        $this->itemClass = modResource::class;
        $c = $this->modx->newQuery($this->itemClass);
        $c->leftJoin(modResource::class, 'Child', ['modResource.id = Child.parent']);
        $c->leftJoin(
            msCategoryMember::class,
            'Member',
            'modResource.id = Member.category_id AND Member.product_id = ' . $this->resource_id
        );
        $c->select($this->modx->getSelectColumns(modResource::class, 'modResource', '', $resourceColumns));
        $c->select([
            'childrenCount' => 'COUNT(Child.id)',
            'member' => 'category_id',
        ]);
        $c->where([
            'context_key' => $this->contextKey,
            'show_in_tree' => true,
            'isfolder' => true,
            'OR:class_key:LIKE' => '%msCategory',
            'AND:context_key:=' => $this->contextKey,
        ]);
        if (empty($this->startNode) && !empty($this->defaultRootId)) {
            $c->where([
                'id:IN' => explode(',', $this->defaultRootId),
                'parent:NOT IN' => explode(',', $this->defaultRootId),
            ]);
        } else {
            $c->where([
                'parent' => $this->startNode,
            ]);
        }
        $c->groupby($this->modx->getSelectColumns(modResource::class, 'modResource', '', $resourceColumns), '');
        $c->sortby('modResource.' . $this->getProperty('sortBy'), $this->getProperty('sortDir'));

        return $c;
    }

    /**
     * @param modContext $context
     *
     * @return array
     */
    public function prepareContextNode(modContext $context)
    {
        $context->prepare();

        return [
            'text' => $context->get('name') != '' ? strip_tags($context->get('name')) : $context->get('key'),
            'id' => $context->get('key') . '_0',
            'pk' => $context->get('key'),
            'ctx' => $context->get('key'),
            'leaf' => false,
            'cls' => 'icon-context',
            'iconCls' => $this->modx->getOption('mgr_tree_icon_context', null, 'tree-context'),
            'qtip' => $context->get('description') != '' ? strip_tags($context->get('description')) : '',
            'type' => 'modContext',
        ];
    }

    /**
     * @param modResource $resource
     *
     * @return array
     */
    public function prepareResourceNode(modResource $resource)
    {
        $qtipField = $this->getProperty('qtipField');
        $nodeField = $this->getProperty('nodeField');
        $nodeFieldFallback = $this->getProperty('nodeFieldFallback');

        $hasChildren = (int)$resource->get('childrenCount') > 0 && $resource->get('hide_children_in_tree') == 0;

        // Assign an icon class based on the class_key
        $class = $iconCls = [];
        $classKey = strtolower($resource->get('class_key'));
        if (substr($classKey, 0, 3) == 'mod') {
            $classKey = substr($classKey, 3);
        }
        $tmp = explode('\\', $resource->get('class_key'));
        $cleanClassKey = $tmp[count($tmp) - 1];
        $classKeyIcon = $this->modx->getOption('mgr_tree_icon_' . strtolower($cleanClassKey), null, 'tree-resource');
        $iconCls[] = $classKeyIcon;



        $class[] = 'icon-' . strtolower(str_replace('mod', '', $cleanClassKey));
        if (!$resource->get('isfolder')) {
            $class[] = 'x-tree-node-leaf icon-resource';
        }
        if (!$resource->get('published')) {
            $class[] = 'unpublished';
        }
        if ($resource->get('deleted')) {
            $class[] = 'deleted';
        }
        if ($resource->get('hidemenu')) {
            $class[] = 'hidemenu';
        }
        if ($hasChildren) {
            $class[] = 'haschildren';
            if ($cleanClassKey !== 'msCategory') {
                $iconCls[] = $this->modx->getOption('mgr_tree_icon_folder', null, 'tree-folder');
            }
            $iconCls[] = 'parent-resource';
        }

        $qtip = '';
        if (!empty($qtipField)) {
            $qtip = '<b>' . strip_tags($resource->$qtipField) . '</b>';
        } else {
            if ($resource->get('longtitle') != '') {
                $qtip = '<b>' . strip_tags($resource->get('longtitle')) . '</b><br />';
            }
            if ($resource->get('description') != '') {
                $qtip = '<i>' . strip_tags($resource->get('description')) . '</i>';
            }
        }

        $idNote = $this->modx->hasPermission('tree_show_resource_ids')
            ? ' (' . $resource->get('id') . ')'
            : '';

        if (!$text = strip_tags($resource->$nodeField)) {
            $text = strip_tags($resource->$nodeFieldFallback);
        }
        $itemArray = [
            'text' => $text . $idNote,
            'id' => $resource->get('context_key') . '_' . $resource->get('id'),
            'pk' => $resource->get('id'),
            'cls' => implode(' ', $class),
            'iconCls' => implode(' ', $iconCls),
            'type' => 'modResource',
            'classKey' => $cleanClassKey,
            'ctx' => $resource->get('context_key'),
            'hide_children_in_tree' => $resource->get('hide_children_in_tree'),
            'qtip' => $qtip,
            'checked' => !empty($resource->member) || $resource->get('id') == $this->parent_id,
            'disabled' => $resource->id == $this->parent_id,
        ];
        if (!$hasChildren) {
            $itemArray['hasChildren'] = false;
            $itemArray['children'] = [];
            $itemArray['expanded'] = true;
        } else {
            $itemArray['hasChildren'] = true;
        }

        if ($itemArray['classKey'] !== 'msCategory') {
            unset($itemArray['checked']);
        }

        return $itemArray;
    }
}
