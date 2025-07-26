<?php
namespace miscreant\securepm\ucp;

class ucp_securepm_info
{
    public function module()
    {
        return [
            'filename'  => 'ucp_securepm',
            'title'     => 'UCP_SECUREPM_TITLE',
            'modes'     => [
                'settings' => [
                    'title' => 'UCP_SECUREPM_TITLE',
                    'auth'  => '',
                ],
            ],
        ];
    }
}
