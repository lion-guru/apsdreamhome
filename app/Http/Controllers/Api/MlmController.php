<?php

namespace App\Http\Controllers\Api;

use \Exception;

class MlmController extends BaseApiController
{
    public function __construct()
    {
        parent::__construct();
        $this->middleware('auth');
        $this->middleware('role:associate', ['except' => ['levels']]);
    }

    /**
     * Get MLM dashboard data
     */
    public function dashboard()
    {
        try {
            $user = $this->auth->user();
            $dashboardData = $this->model('AssociateMLM')->getDashboardData($user->id);
            if (!$dashboardData) {
                return $this->jsonError('Not registered as associate', 404);
            }

            return $this->jsonSuccess($dashboardData);
        } catch (Exception $e) {
            return $this->jsonError($e->getMessage(), 500);
        }
    }

    /**
     * Get genealogy tree
     */
    public function genealogy()
    {
        try {
            $user = $this->auth->user();
            $levels = (int)$this->request()->input('levels', 3);
            $genealogy = $this->model('AssociateMLM')->getGenealogy($user->id, $levels);

            return $this->jsonSuccess($genealogy);
        } catch (Exception $e) {
            return $this->jsonError($e->getMessage(), 500);
        }
    }

    /**
     * Get downline structure
     */
    public function downline()
    {
        try {
            $user = $this->auth->user();
            $levels = (int)$this->request()->input('levels', 3);
            $downline = $this->model('AssociateMLM')->getDownline($user->id, $levels);

            return $this->jsonSuccess($downline);
        } catch (Exception $e) {
            return $this->jsonError($e->getMessage(), 500);
        }
    }

    /**
     * Get MLM level configuration
     */
    public function levels()
    {
        try {
            $levels = $this->model('AssociateMLM')->getLevelConfig();
            return $this->jsonSuccess($levels);
        } catch (Exception $e) {
            return $this->jsonError($e->getMessage(), 500);
        }
    }

    /**
     * Get associate rank and achievements
     */
    public function rank()
    {
        try {
            $user = $this->auth->user();
            $rankInfo = $this->model('AssociateMLM')->getAssociateRank($user->id);
            return $this->jsonSuccess($rankInfo);
        } catch (Exception $e) {
            return $this->jsonError($e->getMessage(), 500);
        }
    }
}
