<?php

namespace App\Http\Controllers;

use App\Services\ClickUpService;

class SettingsController extends Controller
{
    public function index(ClickUpService $clickUpService)
    {
        $clickupUser = $clickUpService->getUser();
        $teams = $clickUpService->getTeams();

        return view('settings.index', compact('clickupUser', 'teams'));
    }
}
