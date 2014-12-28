<?php

require_once($config['root'].'modules/swiftMailer/lib/swift_required.php');


class MailSender {

	function __construct($pConfig){
		$this->transport = Swift_MailTransport::newInstance();
		$this->mailer = Swift_Mailer::newInstance($this->transport);
	}

	public function generateMessage($pParams){
		
		if($this->validateData($pParams)){

			$pParams = $this->completeData($pParams);

			$message = Swift_Message::newInstance($pParams['title'])
			->setFrom(array($pParams['sender']['email'] => $pParams['sender']['name']))
			->setTo(array($pParams['addressee']['email'] => $pParams['addressee']['name']))
			->setBody($pParams['message']);
		    if(count($pParams['filePath']) > 0)
		        foreach($pParams['filePath'] as $lPath)
			
				$message->attach(Swift_Attachment::fromPath($lPath));

			return $message;
		}
		else
			return null;
	}

	public function sendMessage($pMessage){
		return $this->mailer->send($pMessage);
	}

	private function validateData($pParams){
		
		$lResult = true;

		$lResult = 	!empty($pParams['title']) &&
					!empty($pParams['message']) &&
					!empty($pParams['sender']['email']) &&
					!empty($pParams['addressee']['email']);
		
		if(!$lResult)
			print "Data is invalid<br />";
			
		return $lResult;
	}

	private function completeData($pParams){
		if(empty($pParams['sender']['name']))
			$pParams['sender']['name'] = $pParams['sender']['email'];
		if(empty($pParams['sender']['name']))
			$pParams['addressee']['name'] = $pParams['addressee']['email'];

		return $pParams;
	}
}

?>