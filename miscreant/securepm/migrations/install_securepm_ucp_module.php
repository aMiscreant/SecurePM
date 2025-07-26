<?php
namespace miscreant\securepm\migrations;

class install_securepm_ucp_module extends \phpbb\db\migration\migration
{
    public function update_data()
    {
        return [
            ['module.add', ['ucp', 'UCP_PROFILE', [
                'module_basename' => 'ucp_securepm',
                'modes' => ['settings'],
            ]]],
        ];
    }
}
