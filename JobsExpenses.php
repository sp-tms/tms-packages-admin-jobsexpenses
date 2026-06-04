<?php

namespace Apps\Tms\Packages\Jobs\Expenses;

use System\Base\BasePackage;

class JobsExpenses extends BasePackage
{
    //protected $modelToUse = ::class;

    protected $packageName = 'jobsexpenses';

    public $jobsexpenses;

    public function init()
    {
        //Note: If you want to use init function, you need to run parent::init as well.
        //It is used by the use app database feature of the app.
        //if you remove the init() function from this class, it is also fine.
        parent::init();

        return $this;
    }

    public function getJobsExpensesById($id)
    {
        $jobsexpenses = $this->getById($id);

        if ($jobsexpenses) {
            //
            $this->addResponse('Success');

            return;
        }

        $this->addResponse('Error', 1);
    }

    public function addJobsExpenses($data)
    {
        //
    }

    public function updateJobsExpenses($data)
    {
        $jobsexpenses = $this->getById($id);

        if ($jobsexpenses) {
            //
            $this->addResponse('Success');

            return;
        }

        $this->addResponse('Error', 1);
    }

    public function removeJobsExpenses($data)
    {
        $jobsexpenses = $this->getById($id);

        if ($jobsexpenses) {
            //
            $this->addResponse('Success');

            return;
        }

        $this->addResponse('Error', 1);
    }
}