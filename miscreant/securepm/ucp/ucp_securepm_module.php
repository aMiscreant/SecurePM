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

        $publicKey = '';

        if ($request->is_set_post('generate_key'))
        {
            if (!check_form_key('securepm')) {
                trigger_error('FORM_INVALID');
            }

            // Prepare sanitized user info
            $username = escapeshellarg($user->data['username']);
            $email = escapeshellarg($user->data['user_email']);

            // GPG key parameters (batch, no passphrase)
            $keyParams = <<<EOT
%echo Generating a GPG key
Key-Type: RSA
Key-Length: 4096
Name-Real: $username
Name-Email: $email
Expire-Date: 0
%no-protection
%commit
%echo Done
EOT;

            // Store params in a temp file
            $paramsFile = sys_get_temp_dir() . '/securepm_keyparams_' . $user->data['user_id'];
            file_put_contents($paramsFile, $keyParams);

            // Use a temp GPG home dir per user
            $gpgHome = sys_get_temp_dir() . '/securepm_gpg_' . $user->data['user_id'];
            if (!is_dir($gpgHome)) {
                mkdir($gpgHome, 0700, true);
            }

            // Generate the key using batch mode and loopback pinentry
            $cmd = "gpg --homedir $gpgHome --batch --pinentry-mode loopback --generate-key $paramsFile";
            exec($cmd . " 2>&1", $output, $resultCode);

            // Clean up key params file
            unlink($paramsFile);

            if ($resultCode === 0) {
                // List keys in the user's keyring and find one matching username or email
                $listCmd = "gpg --homedir $gpgHome --list-keys --with-colons";
                $listOutput = shell_exec($listCmd);

                $fingerprint = '';
                $lines = explode("\n", $listOutput);
                $currentFpr = '';
                foreach ($lines as $line) {
                    if (strpos($line, 'fpr:') === 0) {
                        // Extract fingerprint
                        $currentFpr = explode(':', $line)[9];
                    }
                    if (strpos($line, 'uid:') === 0) {
                        // Extract user ID (name/email)
                        $uidFields = explode(':', $line);
                        $uidVal = $uidFields[9];
                        // Match username or email
                        if (strpos($uidVal, $user->data['username']) !== false || strpos($uidVal, $user->data['user_email']) !== false) {
                            $fingerprint = $currentFpr;
                            break; // Take the first match
                        }
                    }
                }

                // Export only the matched public key
                if ($fingerprint) {
                    $exportCmd = "gpg --homedir $gpgHome --armor --export $fingerprint";
                    $publicKey = shell_exec($exportCmd);
                } else {
                    $publicKey = "No GPG public key found for your username/email!";
                }

                $template->assign_vars([
                    'U_ACTION' => $this->u_action,
                    'GPG_PUBLIC_KEY' => $publicKey,
                ]);

                trigger_error('GPG key successfully generated. Your public key is shown below.');
            } else {
                trigger_error('Key generation failed:<br>' . implode("<br>", $output));
            }
        } else {
            // On GET, also show the user's public key if it exists
            $gpgHome = sys_get_temp_dir() . '/securepm_gpg_' . $user->data['user_id'];
            if (is_dir($gpgHome)) {
                $listCmd = "gpg --homedir $gpgHome --list-keys --with-colons";
                $listOutput = shell_exec($listCmd);

                $fingerprint = '';
                $lines = explode("\n", $listOutput);
                $currentFpr = '';
                foreach ($lines as $line) {
                    if (strpos($line, 'fpr:') === 0) {
                        $currentFpr = explode(':', $line)[9];
                    }
                    if (strpos($line, 'uid:') === 0) {
                        $uidFields = explode(':', $line);
                        $uidVal = $uidFields[9];
                        if (strpos($uidVal, $user->data['username']) !== false || strpos($uidVal, $user->data['user_email']) !== false) {
                            $fingerprint = $currentFpr;
                            break;
                        }
                    }
                }
                if ($fingerprint) {
                    $exportCmd = "gpg --homedir $gpgHome --armor --export $fingerprint";
                    $publicKey = shell_exec($exportCmd);
                }
            }
            $template->assign_vars([
                'U_ACTION' => $this->u_action,
                'GPG_PUBLIC_KEY' => $publicKey,
            ]);
        }
    }
}