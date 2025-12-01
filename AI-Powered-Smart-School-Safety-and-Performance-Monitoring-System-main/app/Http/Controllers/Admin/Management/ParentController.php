<?php

namespace App\Http\Controllers\Admin\Management;

use App\DataTables\Admin\Management\ParentDataTable;
use App\Helpers\ValidationRules;
use App\Http\Controllers\Admin\BaseManagementController;
use App\Repositories\Interfaces\Admin\Management\ParentRepositoryInterface;
use Illuminate\Http\Request;

class ParentController extends BaseManagementController
{
    protected string $parentViewPath = 'admin.pages.management.parents.';
    protected string $parentRoutePath = 'admin.management.parents.';
    protected string $entityName = 'Parent';
    protected string $entityType = 'parent';

    public function __construct(ParentRepositoryInterface $repository)
    {
        parent::__construct($repository);
    }

    /**
     * Display entity index page with DataTable
     */
    public function index(ParentDataTable $datatable)
    {
        return $this->renderIndex($datatable, $this->parentViewPath);
    }

    protected function getFormData($id = null): array
    {
        return [];
    }

    protected function getValidationRules(bool $isUpdate = false, $id = null): array
    {
        return ValidationRules::getParentRules($isUpdate, $id);
    }

    protected function performCreate(Request $request)
    {
        // For ParentController, we only show existing parents
        // Creation is typically handled through StudentController
        throw new \Exception('Parent creation not supported directly.');
    }

    protected function performUpdate(Request $request, $id)
    {
        // For ParentController, we only show existing parents
        // Updates are typically handled through StudentController
        throw new \Exception('Parent updates not supported directly.');
    }
}
