<?php

namespace Apps\Tms\Packages\Jobs\Expenses;

use Apps\Tms\Packages\Jobs\Expenses\Model\AppsTmsJobsExpenses;
use Apps\Tms\Packages\Tools\Expenses\ToolsExpenses;
use Apps\Tms\Packages\Tools\Uom\ToolsUom;
use System\Base\BasePackage;

class JobsExpenses extends BasePackage
{
    protected $modelToUse = AppsTmsJobsExpenses::class;

    protected $packageName = 'jobsexpenses';

    public $jobsexpenses;

    public function init()
    {
        parent::init();

        return $this;
    }

    public function addJobsExpense($data)
    {
        if (isset($data['expense_id_new'])) {
            $expenses = $this->addNewExpense($data);
        }

        if (!isset($data['expense_id']) ||
            (isset($data['expense_id']) && $data['expense_id'] === '')
        ) {
            $this->addResponse('Please provide expense', 1);

            return false;
        }

        if (isset($data['quantity_uom_id_new']) || isset($data['rate_uom_id_new'])) {
            $uoms = $this->addNewUom($data);
        }

        if ($this->add($data)) {
            $newExpense = $this->packagesData->last;

            $responseData['newExpense'] = $newExpense;

            if (isset($expenses)) {
                $responseData['expenses'] = $expenses;
            }
            if (isset($uoms)) {
                $responseData['uoms'] = $uoms;
            }

            $this->addResponse('Expense added!', 0, $responseData);

            return true;
        }

        $this->addResponse('Unable to add expense', 1);

        return false;
    }

    public function updateJobsExpense($data)
    {
        $expense = $this->getById((int) $data['id']);

        if (!$expense) {
            $this->addResponse('Expense with ID not found!', 1);

            return false;
        }

        if (isset($data['expense_id_new'])) {
            $expenses = $this->addNewExpense($data);
        }

        if (isset($data['quantity_uom_id_new']) || isset($data['rate_uom_id_new'])) {
            $uoms = $this->addNewUom($data);
        }

        if ($this->update($data)) {
            $updatedExpense = $this->packagesData->last;

            $responseData['updatedExpense'] = $updatedExpense;

            if (isset($expenses)) {
                $responseData['expenses'] = $expenses;
            }
            if (isset($uoms)) {
                $responseData['uoms'] = $uoms;
            }

            $this->addResponse('Expense updated!', 0, $responseData);

            return true;
        }

        $this->addResponse('Unable to update expense', 1);

        return false;
    }

    protected function addNewExpense(&$data)
    {
        $toolsExpensesPackage = $this->usePackage(ToolsExpenses::class);

        $newToolsExpense = [];

        $newToolsExpense['type'] = 1;
        if (str_starts_with(strtolower($data['expense_id']), 'reimburse')) {
            $newToolsExpense['type'] = 2;
        }

        if (str_contains($data['expense_id'], ':')) {
            $expenseIdArr = explode(':', $data['expense_id']);
            $data['expense_id'] = strtoupper(trim($expenseIdArr[1]));
        }

        $newToolsExpense['name'] = strtoupper($data['expense_id']);
        $newToolsExpense['archived'] = 0;
        $newToolsExpense['description'] = strtoupper($data['expense_id']);

        if ($toolsExpensesPackage->addExpense($newToolsExpense)) {
            $data['expense_id'] = $toolsExpensesPackage->packagesData->last['id'];
        } else {
            $data['expense_id'] = '';
        }

        $expenses = $toolsExpensesPackage->getAll()->toolsexpenses;
        if (count($expenses) > 0) {
            $expenses = msort(array: $expenses, key: 'type', preserveKey: true);

            foreach ($expenses as &$expense) {
                if ($expense['type'] == 1) {
                    $expense['display_name'] = 'ADVANCE: ' . $expense['name'];
                } else if ($expense['type'] == 2) {
                    $expense['display_name'] = 'REIMBURSE: ' . $expense['name'];
                }
            }
        }

        return $expenses;
    }

    protected function addNewUom(&$data)
    {
        $toolsUomPackage = $this->usePackage(ToolsUom::class);

        if (isset($data['quantity_uom_id_new'])) {
            if ($toolsUomPackage->addUom(['name' => $data['quantity_uom_id'], 'archived' => '0', 'description' => $data['quantity_uom_id']])) {
                $data['quantity_uom_id'] = $toolsUomPackage->packagesData->last['id'];
            } else {
                $data['quantity_uom_id'] = '';
            }
        }
        if (isset($data['rate_uom_id_new'])) {
            if ($toolsUomPackage->addUom(['name' => $data['rate_uom_id'], 'archived' => '0', 'description' => $data['rate_uom_id']])) {
                $data['rate_uom_id'] = $toolsUomPackage->packagesData->last['id'];
            } else {
                $data['rate_uom_id'] = '';
            }
        }

        return $toolsUomPackage->getAll()->toolsuom;
    }

    public function removeJobsExpense($data)
    {
        $expense = $this->getById((int) $data['id']);

        if (!$expense) {
            $this->addResponse('Expense with ID not found!', 1);

            return false;
        }

        if ($this->remove((int) $data['id'])) {
            $this->addResponse('Expense removed!');

            return false;
        }

        $this->addResponse('Unable to remove expense', 1);

        return false;
    }

    public function checkCarryForwardedExpenses($data)
    {
        if ($this->config->databasetype === 'db') {
            $params =
                [
                    'conditions'    => 'employee_id = :employee_id: AND carry_forwarded = :cf:',
                    'bind'          =>
                        [
                            'employee_id'   => (int) $data['employee_id'],
                            'cf'            => true,
                        ]
                ];
        } else {
            $params = ['conditions' => [['employee_id', '=', (int) $data['employee_id']], ['carry_forward', '=', true]]];
        }

        $carryForwardedExpensesArr = $this->getByParams($params);
        $lorryReceipts = [];
        $carryForwardedExpenses = [];

        if ($carryForwardedExpensesArr && count($carryForwardedExpensesArr) > 0) {
            foreach ($carryForwardedExpensesArr as $carryForwardedExpense) {
                if (!(in_array($carryForwardedExpense['lr_no'], $lorryReceipts))) {
                    array_push($lorryReceipts, $carryForwardedExpense['lr_no']);
                }
                $carryForwardedExpenses[$carryForwardedExpense['id']] = $carryForwardedExpense;
            }

            $responseData = ['carryForwardedExpenses' => $carryForwardedExpenses, 'lorryReceipts' => $lorryReceipts];

            $this->addResponse('Imported expenses successfully.', 0, $responseData);

            return $carryForwardedExpenses;
        }

        return false;
    }

    public function getExpenseTransactionTypes()
    {
        return
            [
                '1' =>
                    [
                        'id' => '1',
                        'name'  => 'Cash'
                    ],
                '2' =>
                    [
                        'id' => '2',
                        'name'  => 'Online'
                    ]
            ];
    }
}