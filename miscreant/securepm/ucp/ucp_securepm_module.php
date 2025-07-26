<?php
namespace miscreant\securepm\ucp;

class ucp_securepm_module
{
    public $u_action;
    public $tpl_name;
    public $page_title;

    public function main($id, $mode)
    {
        global $template, $user, $request;

        $this->tpl_name = 'ucp_securepm_body';
        $this->page_title = 'Secure PM';

        add_form_key('securepm');

        if ($request->is_set_post('generate_key'))
        {
            if (!check_form_key('securepm')) {
                trigger_error('FORM_INVALID');
            }

            $gpg = new \gnupg();
            $keyParams = "Key-Type: RSA\nKey-Length: 4096\nName-Real: " . $user->data['username'] . "\nName-Email: " . $user->data['user_email'] . "\nExpire-Date: 0";
            $key = $gpg->genkey($keyParams);

            if ($key) {
                trigger_error('GPG key successfully generated.');
            } else {
                trigger_error('Key generation failed.');
            }
        }

        $template->assign_vars([
            'U_ACTION' => $this->u_action,
        ]);
    }
}
