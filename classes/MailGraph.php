<?php
class MailGraph
{
    public $from = null;
    public $subject = "";
    public $contentType = "Text";
    public $message = "";    
    public $toRecipients = array();
    public $ccRecipients = array();
    public $bccRecipients = array();
    public $saveToSentItems = "true";
    
    private $graph_scope = "https://graph.microsoft.com/.default";
    private $graph_token_url = "https://login.microsoftonline.com/".AD_TENANT."/oauth2/v2.0/token";
    private $graph_send_mail_url = "";
    
    public function __construct($indirizzo_invio) {
        $this->graph_send_mail_url = "https://graph.microsoft.com/v1.0/users/".$indirizzo_invio."/SendMail";
    }
    
    private function getAccesToken(){        
        $post_params = array(
            "client_id" => OAUTH2_CLIENT_ID,
            "scope" => $this->graph_scope,
            "client_secret" => OAUTH2_CLIENT_SECRET,
            "grant_type" => "client_credentials",    
        );

        $curl_token = curl_init($this->graph_token_url);
        curl_setopt($curl_token, CURLOPT_POST, true);
        curl_setopt($curl_token, CURLOPT_POSTFIELDS, $post_params);
        curl_setopt($curl_token, CURLOPT_HTTPHEADER, array("application/x-www-form-urlencoded"));
        curl_setopt($curl_token, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($curl_token, CURLOPT_RETURNTRANSFER, true);

        return json_decode(curl_exec($curl_token));
    }
    
    private function getMailJson() {
        $mail["message"]["subject"] = $this->subject;
        if ($this->from !== null) {
            $mail["message"]["from"]["emailAddress"]["address"] = $this->from;
        }
        $mail["message"]["body"]["contentType"] = $this->contentType;
        $mail["message"]["body"]["content"] = $this->message;
        $mail["message"]["toRecipients"] = array();
        foreach ($this->toRecipients as $recipient) {
            $mail["message"]["toRecipients"][]["emailAddress"]["address"] = $recipient;    
        }
        foreach ($this->ccRecipients as $recipient) {
            $mail["message"]["ccRecipients"][]["emailAddress"]["address"] = $recipient;    
        }
        foreach ($this->bccRecipients as $recipient) {
            $mail["message"]["bccRecipients"][]["emailAddress"]["address"] = $recipient;    
        }
        $mail["saveToSentItems"] = $this->saveToSentItems;
        
        return json_encode($mail);
    }
    
    public function send() {                
        $token = $this->getAccesToken();
        
        $ch = curl_init($this->graph_send_mail_url);    
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        $headers = array();
        $headers[] = 'Content-Type:application/json';
        $headers[] = 'Accept: application/json';
        $headers[] = 'Authorization: Bearer ' . $token->access_token;
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $this->getMailJson());

        $response = curl_exec($ch);
        curl_close($ch);
        
        return $response;
    }
}
