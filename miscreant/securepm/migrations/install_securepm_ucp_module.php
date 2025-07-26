<?php
namespace aMiscreant\SecurePM\migrations;

class install_securepm_ucp_module extends \phpbb\db\migration\migration
{
    public function update_data()
    {
        return [
            ['module.add', [
                'ucp',
                'UCP_PROFILE',
                [
                    'module_basename' => '\\aMiscreant\\SecurePM\\ucp\\ucp_securepm_module',
                    'modes' => ['settings'],
                ]
            ]],
        ];
    }
}
