<?php

namespace App\Http\Controllers\App\Dashboard;

use App\Actions\Dashboard\DashboardIndexAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\App\Dashboard\DashboardIndexRequest;

class DashboardIndexController extends Controller
{
    public function __construct(
        protected DashboardIndexAction $indexAction
    ) {}

    public function index(DashboardIndexRequest $DashboardIndexRequest)
    {
        $dashboardData = $this->indexAction->exec();
        
        return view('app.dashboard.index', compact('dashboardData'));
    }
}
