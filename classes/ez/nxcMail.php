<?php
/**
 * @author vd
 * @copyright Copyright (C) 2011 NXC AS.
 * @license GNU GPL v2
 * @pakacge nxc_tools
 */

/**
 * Class to send emails using eZComponents and eZMail
 */
class nxcMail
{
    /**
     * Sends simple email as html
     *
     * @param (string) $emailSender
     * @param (array) $recieverList
     * @param (string) $subject
     * @param (string) $content
     *
     * @return (void)
     * @exception nxcRunTimeException
     */
    public function send( $emailSender, $recieverList, $subject, $content )
    {
        try
        {
            $mail = new ezcMail();
            $mail->from = new ezcMailAddress( $emailSender );
            foreach ( $recieverList as $reciever )
            {
                $mail->addTo( new ezcMailAddress( $receiver ) );
            }

            $mail->subject = $subject;
            $textPart = new ezcMailText( $content );
            $textPart->subType = 'html';
            $mail->body = $textPart;
            $ezmail = new eZMail();
            $ezmail->Mail = $mail;
            $ini = eZINI::instance();
            $transportType = trim( $ini->variable( 'MailSettings', 'Transport' ) );
            switch ( $transportType )
            {
                case 'sendmail':
                {
                    $transport = new ezcMailMtaTransport();
                    $transport->send( $mail );
                } break;
                default:
                {
                    eZMailTransport::send( $ezmail );
                } break;
            }
        }
        catch ( Exception $e )
        {
            throw new nxcRunTimeException( $e->getMessage() );
        }
    }
}

?>
