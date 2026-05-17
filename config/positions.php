<?php

return [
    // Department to prefix mapping
    'department_prefixes' => [
        'HR'  => 'HR',
        'IT'  => 'IT',
        'FIN' => 'FIN',
        'OPS' => 'OPS',
    ],

    // Position ID mapping: department_code => position_id => position_name
    'position_ids' => [
        'HR' => [
            2000 => 'HR Administrator',
            2001 => 'HR Specialist',
            2002 => 'HR Officer',
            2003 => 'HR Head',
        ],
        'IT' => [
            3000 => 'IT Manager',
            3001 => 'Senior Developer',
            3002 => 'Developer',
            3003 => 'QA Engineer',
            3004 => 'Technical Support',
        ],
        'FIN' => [
            4000 => 'Finance Manager',
            4001 => 'Accountant',
            4002 => 'Financial Analyst',
        ],
        'OPS' => [
            5000 => 'Operations Manager',
            5001 => 'Operations Lead',
            5002 => 'Operations Staff',
        ],
    ],

    // Legacy: Department names to codes (for reference/migration)
    'departments' => [
        'IT Department' => [
            'IT Manager',
            'Senior Developer',
            'Developer',
            'QA Engineer',
        ],
        'Finance Department' => [
            'Finance Manager',
            'Accountant',
            'Financial Analyst',
        ],
        'Human Resources Department' => [
            'HR Administrator',
            'HR Specialist',
            'HR Officer',
        ],
        'Operations Department' => [
            'Operations Manager',
            'Operations Lead',
            'Operations Staff',
        ],
        'Marketing Department' => [
            'Marketing Manager',
            'Marketing Specialist',
            'Content Writer',
        ],
    ],

    'all_positions' => [
        'IT Manager',
        'Senior Developer',
        'Developer',
        'QA Engineer',
        'Finance Manager',
        'Accountant',
        'Financial Analyst',
        'HR Administrator',
        'HR Specialist',
        'HR Officer',
        'Operations Manager',
        'Operations Lead',
        'Operations Staff',
        'Marketing Manager',
        'Marketing Specialist',
        'Content Writer',
    ],
];
