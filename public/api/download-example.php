<?php
/**
 * Download Example Import Files
 * Provides downloadable CSV and JSON template files for import
 */

require_once dirname(__DIR__, 2) . '/config/config.php';

Auth::requireLogin();
RBAC::requireOrganisationAdmin();

$type = $_GET['type'] ?? '';
$format = $_GET['format'] ?? 'csv';

// Validate type
if (!in_array($type, ['units', 'members', 'users'])) {
    http_response_code(400);
    die('Invalid type. Must be "units", "members", or "users".');
}

// Validate format
if (!in_array($format, ['csv', 'json'])) {
    http_response_code(400);
    die('Invalid format. Must be "csv" or "json".');
}

// Generate filename
$filename = "example_{$type}_{$format}." . ($format === 'csv' ? 'csv' : 'json');

if ($format === 'csv') {
    if ($type === 'units') {
        // CSV example for organisational units
        $csv = "name,unit_type,parent,description\n";
        $csv .= "North Region,region,,Regional grouping for northern areas\n";
        $csv .= "South Region,region,,Regional grouping for southern areas\n";
        $csv .= "Newcastle Area,area,North Region,Newcastle area covering multiple teams\n";
        $csv .= "Leeds Area,area,North Region,Leeds area covering multiple teams\n";
        $csv .= "Newcastle Team,team,Newcastle Area,Acute care team in Newcastle\n";
        $csv .= "Newcastle Admin,team,Newcastle Area,Administrative team\n";
        $csv .= "Leeds Team,team,Leeds Area,Acute care team in Leeds\n";
        
        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        echo $csv;
    } else {
        // CSV example for member assignments
        $csv = "email,unit_name,role\n";
        $csv .= "john.doe@example.com,Newcastle Team,member\n";
        $csv .= "jane.smith@example.com,Newcastle Team,lead\n";
        $csv .= "bob.jones@example.com,Leeds Team,member\n";
        $csv .= "alice.brown@example.com,Newcastle Admin,member\n";
        
        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        echo $csv;
    }
} else {
    // JSON format
    if ($type === 'units') {
        // JSON example for organisational units with members
        $json = [
            'units' => [
                [
                    'name' => 'North Region',
                    'unit_type' => 'region',
                    'description' => 'Regional grouping for northern areas',
                    'children' => [
                        [
                            'name' => 'Newcastle Area',
                            'unit_type' => 'area',
                            'description' => 'Newcastle area covering multiple teams',
                            'members' => [
                                ['email' => 'area.manager@example.com', 'role' => 'lead']
                            ],
                            'children' => [
                                [
                                    'name' => 'Newcastle Team',
                                    'unit_type' => 'team',
                                    'description' => 'Acute care team in Newcastle',
                                    'members' => [
                                        ['email' => 'john.doe@example.com', 'role' => 'member'],
                                        ['email' => 'jane.smith@example.com', 'role' => 'lead']
                                    ]
                                ],
                                [
                                    'name' => 'Newcastle Admin',
                                    'unit_type' => 'team',
                                    'description' => 'Administrative team',
                                    'members' => [
                                        ['email' => 'alice.brown@example.com', 'role' => 'member']
                                    ]
                                ]
                            ]
                        ],
                        [
                            'name' => 'Leeds Area',
                            'unit_type' => 'area',
                            'description' => 'Leeds area covering multiple teams',
                            'children' => [
                                [
                                    'name' => 'Leeds Team',
                                    'unit_type' => 'team',
                                    'description' => 'Acute care team in Leeds',
                                    'members' => [
                                        ['email' => 'bob.jones@example.com', 'role' => 'member']
                                    ]
                                ]
                            ]
                        ]
                    ]
                ],
                [
                    'name' => 'South Region',
                    'unit_type' => 'region',
                    'description' => 'Regional grouping for southern areas',
                    'children' => []
                ]
            ]
        ];
        
        header('Content-Type: application/json');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        echo json_encode($json, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
    } elseif ($type === 'members') {
        // JSON example for member assignments only
        $json = [
            'assignments' => [
                [
                    'email' => 'john.doe@example.com',
                    'unit_name' => 'Newcastle Team',
                    'role' => 'member'
                ],
                [
                    'email' => 'jane.smith@example.com',
                    'unit_name' => 'Newcastle Team',
                    'role' => 'lead'
                ],
                [
                    'email' => 'bob.jones@example.com',
                    'unit_name' => 'Leeds Team',
                    'role' => 'member'
                ],
                [
                    'email' => 'alice.brown@example.com',
                    'unit_name' => 'Newcastle Admin',
                    'role' => 'member'
                ]
            ]
        ];
        
        header('Content-Type: application/json');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        echo json_encode($json, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
    } else {
        // JSON example for users
        $json = [
            'users' => [
                [
                    'email' => 'john.doe@example.com',
                    'first_name' => 'John',
                    'last_name' => 'Doe',
                    'employee_reference' => 'EMP001'
                ],
                [
                    'email' => 'jane.smith@example.com',
                    'first_name' => 'Jane',
                    'last_name' => 'Smith',
                    'employee_reference' => 'EMP002'
                ],
                [
                    'email' => 'bob.jones@example.com',
                    'first_name' => 'Bob',
                    'last_name' => 'Jones',
                    'employee_reference' => 'EMP003'
                ],
                [
                    'email' => 'alice.brown@example.com',
                    'first_name' => 'Alice',
                    'last_name' => 'Brown',
                    'employee_reference' => 'EMP004'
                ]
            ]
        ];
        
        header('Content-Type: application/json');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        echo json_encode($json, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
    }
}

