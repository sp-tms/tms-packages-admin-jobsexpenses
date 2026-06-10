<?php

namespace Apps\Tms\Packages\Jobs\Expenses\Model;

use System\Base\BaseModel;

class AppsTmsJobsExpenses extends BaseModel
{
    public $id;

    public $lr_no;

    public $voucher_no;

    public $employee_id;

    public $expense_id;

    public $expense_date;

    public $mode_of_payment;

    public $payment_tx_id;

    public $quantity;

    public $quantity_uom_id;

    public $rate;

    public $rate_uom_id;

    public $amount;

    public $carry_forward;
}