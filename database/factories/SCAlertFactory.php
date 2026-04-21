<?php

namespace Database\Factories;

use App\Models\SCAlert;
use App\Models\SCEndpoint;
use App\Models\SCFirewall;
use App\Models\SCTenant;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<SCAlert>
 */
class SCAlertFactory extends Factory
{

    private const REAL_ACTIONS = ['acknowledge', 'clearThreat'];


    private const REAL_ENDPOINT_SETS = [

        [
            'category' => 'pua',
            'description' => 'Manual PUA cleanup required: \'PsExec\' at \'C:\\Users\\Someuser\\AppData\\Local\\Apps\\\\micr...exe_b1d1a6c45aa418ce_0011.0000_none_521f3d76203e6140\\WPJCleanUp.zip\'',
            'product' => 'endpoint',
            'type' => 'Event::Endpoint::CorePuaCleanFailed',
        ],
        [
            'category' => 'pua',
            'description' => 'PUA \'Generic ML PUA\' detected in network location \'\\\\SomeExeFile.\' requires attention',
            'product' => 'endpoint',
            'type' => 'Event::Endpoint::CorePuaRemoteDetection',
        ],
        [
            'category' => 'appReputation',
            'description' => 'Low reputation app detected: \'---\' at \'de.fee.fwmobile\'',
            'product' => 'endpoint',
            'type' => 'Event::Endpoint::Threat::LowRepAppDetected',
        ],
        [
            'category' => 'protection',
            'description' => 'Device has been detected as a duplicate device, for more information see knowledge base article <a target="_blank" href="https://community.sophos.com/kb/en-us/132029">132029</a>.',
            'product' => 'endpoint',
            'type' => 'Event::Endpoint::CloneDetected',
        ],
        [
            'category' => 'policy',
            'description' => 'Real time protection disabled',
            'product' => 'endpoint',
            'type' => 'Event::Endpoint::SavDisabled',
        ],
        [
            'category' => 'utm',
            'description' => 'Sophos Firewall ASDLKASD reported computer not sending heartbeat signals',
            'product' => 'endpoint',
            'type' => 'Event::Endpoint::HeartbeatMissing',
        ]
    ];

    private const REAL_FIREWALL_SETS = [
                [
                    'category' => 'security',
                    'description' => 'We detected an attempt to communicate with threat or botnet MalwareServerblocklist, from source 192.168.16.1 to destination somewhere.over.the-rainbow.dev(192.168.16.10).',
                    'product' => 'firewall',
                    'type' => 'Event::Firewall::FirewallAdvancedThreatProtectionDetailed',
                    'allowedActions' => ['acknowledge'],
                ],
                [
                    'category' => 'security',
                    'description' => 'We detected an attempt to communicate with a botnet or command and control server.',
                    'product' => 'firewall',
                    'type' => 'Event::Firewall::FirewallAdvancedThreatProtection',
                    'allowedActions' => ['acknowledge'],
                ],
                [
                    'category' => 'connectivity',
                    'description' => "Gateway 'WAN' is Up",
                    'product' => 'firewall',
                    'type' => 'Event::Firewall::FirewallGatewayUp',
                    'allowedActions' => ['acknowledge'],
                ],
                [
                    'category' => 'connectivity',
                    'description' => "Gateway 'WAN' is Down",
                    'product' => 'firewall',
                    'type' => 'Event::Firewall::FirewallGatewayDown',
                    'allowedActions' => ['acknowledge'],
                ],
                [
                    'category' => 'connectivity',
                    'description' => "Some Tunnel - IPSec Connection SomeName between xx.xx.xx.xx and yy.yy.yy.yy for Child SomeName established. (Remote: yy.yy.yy.yy)",
                    'product' => 'firewall',
                    'type' => 'Event::Firewall::FirewallVPNTunnelUp',
                    'allowedActions' => ['acknowledge'],
                ],
                [
                    'category' => 'connectivity',
                    'description' => "Some Tunnel - IPSec Connection SomeName between xx.xx.xx.xx and yy.yy.yy.yy for Child SomeName established. (Remote: yy.yy.yy.yy)",
                    'product' => 'firewall',
                    'type' => 'Event::Firewall::FirewallVPNTunnelUp',
                    'allowedActions' => ['acknowledge'],
                ],
                [
                    'category' => 'security',
                    'description' => 'We detected an attempt to communicate with a botnet or command and control server.',
                    'product' => 'firewall',
                    'type' => 'Event::Firewall::FirewallAdvancedThreatProtection',
                    'allowedActions' => ['acknowledge'],
                ]
    ];

    public function definition(): array
    {

        $rawData = [
            'id' => (string) Str::uuid(),

            'allowedActions' => $this->faker->randomElements(self::REAL_ACTIONS, rand(1, count(self::REAL_ACTIONS))),
            
            'category' => "",
            'description' => "",
            'product' => "",     
            'type' => "",
            
            'tenant' => [
                'id' => (string) Str::uuid(),
                'name' => $this->faker->company(),
                'dataRegion' => $this->faker->word(),
            ],     
            'groupKey' => (string) Str::uuid(),
            'person' => [
                'id' => (string) Str::uuid(),
                'name' => $this->faker->name(),
            ],
            'raisedAt' => $this->faker->dateTimeThisYear(),
            'severity' => $this->faker->randomElement(['low', 'medium', 'high']),
        ];


        return [
            'id' => $rawData['id'],
            'allowedActions' => $rawData['allowedActions'],
            'category' => $rawData['category'],
            'description' => $rawData['description'],
            'groupKey' => $rawData['groupKey'],
            'managedAgentID' => '',
            'managedAgentName' => '',
            'managedAgentType' => '',
            'personID' => $rawData['person']['id'],
            'personName' => $rawData['person']['name'],
            'product' => $rawData['product'],
            'raisedAt' => $rawData['raisedAt'],
            'severity' => $rawData['severity'],
            'tenantId' => $rawData['tenant']['id'],
            'type' => $rawData['type'],
            'rawData' => $rawData,
            'webhook_sent' => random_int(0,1),
            'is_acknowledged' => random_int(0,1),
            'updated_at' => $rawData['raisedAt'],
        ];
    }

    public function forFirewall(SCFirewall $firewall){
        $set = $this->faker->randomElement(self::REAL_FIREWALL_SETS);
        return $this->state(function (array $attributes) use ($set, $firewall) {
            return [
                 'rawData' => array_merge($attributes['rawData'], [
                     'managedAgent' => [
                        'id' => $firewall->id,
                        'name' => $firewall->hostname,
                        'type' => $firewall->type,
                    ],
                    'tenant' => [
                        'id' => $firewall->tenant->id,
                        'name' => $firewall->tenant->name,
                        'dataRegion' => $firewall->tenant->dataRegion,
                    ],
                    'category' => $set['category'],
                    'description' => $set['description'],
                    'product' => $set['product'],
                    'type' => $set['type'],
                    'allowedActions' => $set['allowedActions'] ?? [],
                ]),
                'managedAgentID' => $firewall->id,
                'managedAgentName' => $firewall->name,
                'managedAgentType' => 'firewall',

                'tenantId' => $firewall->tenant->id,
                
                'category' => $set['category'],
                'description' => $set['description'],
                'product' => $set['product'],
                'type' => $set['type'],
                'allowedActions' => $set['allowedActions'] ?? [],

            ];
        });
    }

    public function forEndpoint(SCEndpoint $endpoint){
        $set = $this->faker->randomElement(self::REAL_ENDPOINT_SETS);
         return $this->state(function (array $attributes) use ($set, $endpoint) {
            return [
                 'rawData' => array_merge($attributes['rawData'], [
                     'managedAgent' => [
                        'id' => $endpoint->id,
                        'name' => $endpoint->hostname,
                        'type' => $endpoint->type,
                    ],
                    'tenant' => [
                        'id' => $endpoint->tenant->id,
                        'name' => $endpoint->tenant->name,
                        'dataRegion' => $endpoint->tenant->dataRegion,
                    ],
                    'category' => $set['category'],
                    'description' => $set['description'],
                    'product' => $set['product'],
                    'type' => $set['type'],
                    'allowedActions' => $set['allowedActions'] ?? [],
                ]),
                'managedAgentID' => $endpoint->id,
                'managedAgentName' => $endpoint->name,
                'managedAgentType' => 'endpoint',

                'tenantId' => $endpoint->tenant->id,

                'category' => $set['category'],
                'description' => $set['description'],
                'product' => $set['product'],
                'type' => $set['type'],
                'allowedActions' => $set['allowedActions'] ?? [],
            ];
        });
    }
}
