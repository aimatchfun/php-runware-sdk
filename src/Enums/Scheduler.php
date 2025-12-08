<?php

namespace AiMatchFun\PhpRunwareSDK;

enum Scheduler: string
{
    case EULER = 'Euler';
    case EULER_A = 'EulerAncestralDiscreteScheduler';
    case FLOW_MATCH_EULER = 'FlowMatch Euler';
    case DPM_PLUS_PLUS = 'DPM++';
    case DPM_PLUS_PLUS_SDE = 'DPM++ SDE';
    case DPM_PLUS_PLUS_2M = 'DPM++ 2M';
    case DPM_PLUS_PLUS_2M_SDE = 'DPM++ 2M SDE';
    case DPM_PLUS_PLUS_3M = 'DPM++ 3M';
    case EULER_BETA = 'Euler Beta';
    case EULER_EXPONENTIAL = 'Euler Exponential';
    case EULER_KARRAS = 'Euler Karras';
    case DPM_PLUS_PLUS_BETA = 'DPM++ Beta';
    case DPM_PLUS_PLUS_EXPONENTIAL = 'DPM++ Exponential';
    case DPM_PLUS_PLUS_KARRAS = 'DPM++ Karras';
    case DPM_PLUS_PLUS_SDE_BETA = 'DPM++ SDE Beta';
    case DPM_PLUS_PLUS_SDE_EXPONENTIAL = 'DPM++ SDE Exponential';
    case DPM_PLUS_PLUS_SDE_KARRAS = 'DPM++ SDE Karras';
    case DPM_PLUS_PLUS_2M_BETA = 'DPM++ 2M Beta';
    case DPM_PLUS_PLUS_2M_EXPONENTIAL = 'DPM++ 2M Exponential';
    case DPM_PLUS_PLUS_2M_KARRAS = 'DPM++ 2M Karras';
    case DPM_PLUS_PLUS_2M_SDE_BETA = 'DPM++ 2M SDE Beta';
    case DPM_PLUS_PLUS_2M_SDE_EXPONENTIAL = 'DPM++ 2M SDE Exponential';
    case DPM_PLUS_PLUS_2M_SDE_KARRAS = 'DPM++ 2M SDE Karras';
    case DPM_PLUS_PLUS_3M_BETA = 'DPM++ 3M Beta';
    case DPM_PLUS_PLUS_3M_EXPONENTIAL = 'DPM++ 3M Exponential';
    case DPM_PLUS_PLUS_3M_KARRAS = 'DPM++ 3M Karras';
    case DDIM = 'DDIM';
    case DDPM = 'DDPM';
    case DEIS_MULTISTEP = 'DEIS Multistep';
    case DPM_SOLVER_SINGLE_STEP = 'DPM-Solver Single-step';
    case DPM_SOLVER_MULTI_STEP = 'DPM-Solver Multi-step';
    case DPM_SOLVER_MULTI_STEP_INVERSE = 'DPM-Solver Multi-step Inverse';
    case EDM_EULER = 'EDM Euler';
    case EDM_DPM_SOLVER_MULTI_STEP = 'EDM DPM-Solver Multi-step';
    case HEUN = 'Heun';
    case IPNDM = 'IPNDM';
    case KDPM2 = 'KDPM2';
    case KDPM2_ANCESTRAL = 'KDPM2 Ancestral';
    case LCM = 'LCM';
    case LMS = 'LMS';
    case PNDM = 'PNDM';
    case TCD = 'TCD';
    case UNIPC = 'UniPC';
    case UNIPC_MULTISTEP = 'UniPC Multistep';

    /**
     * Get all available FLUX schedulers
     *
     * @return array
     */
    public static function getAll(): array
    {
        return array_map(fn($case) => $case->value, self::cases());
    }

    /**
     * Check if a scheduler is valid for FLUX
     *
     * @param string $scheduler
     * @return bool
     */
    public static function isValid(string $scheduler): bool
    {
        return in_array($scheduler, self::getAll());
    }
}