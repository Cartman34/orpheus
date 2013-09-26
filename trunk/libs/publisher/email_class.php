<?php
//! The email class
/*!
 * This class is a tool to send mails.
 * 	
 * Incompatible with Rev < 552
 */
class Email {
	//Attributes
	private $Headers = array(
		'MIME-Version' => '',
		'Content-Type' => 'text/plain, charset=UTF-8',
		//'Content-Transfer-Encoding' => '7bit',
		'Date' => '',//See init()
		'From' => 'no-reply@nodomain.com',//Override PHP's default
		'Sender' => '',
		'X-Sender' => '',
		'Reply-To' => '',//Return Email Address
		'Return-Path' => '',//Return Email Address
		'Organization' => '',
		'X-Priority' => '3',
		'X-Mailer' => 'Orpheus\'s Mailer',
		'Bcc' => '',
	);
	
	private $HTMLBody;
	private $TEXTBody;
	private $AltBody;
	
	/* Attached Files
	Contains a list of file names
	*/
	private $AttFiles = array();
	
	private $Subject;
	private $Type=0;// Bit value, 1=>Text, 2=>HTML
	private $MIMEBoundary = array();
	
	public static $TEXTTYPE = 1;
	public static $HTMLTYPE = 2;

	//Methods
	
	//! Constructor
	/*!
	 * \param $TEXTBody The body of the text message. Default value is an empty string.
	 * \param $Subject The subject of the mail. Default value is an empty string.
	 */
	public function __construct($Subject='', $Text='') { //Class' Constructor
		$this->init();
		$this->setSubject($Subject);
		$this->setText($Text);
	}
	
	//! Initializes the object
	private function init() {
		$this->Headers['Date'] = date('r');
		if( defined('REPLYEMAIL') ) {
			$sendEmail = REPLYEMAIL;
		} else if( defined('ADMINEMAIL') ) {
			$sendEmail = ADMINEMAIL;
		} else {
			return;
		}
		if( defined('SITENAME') ) {
			$this->setSender(SITENAME.' <'.$sendEmail.'>');
		} else {
			$this->setSender($sendEmail);
		}
	}
	
	//! Sets the value of a header
	/*!
	 * \param $Key The key of the header to set.
	 * \param $Value The new value of the header.
	 */
	public function setHeader($Key, $Value) {
		if( !isset($this->Headers[$Key]) ) {
			throw new Exception('UnknownHeader');
			return false;
		}
		$this->Headers[$Key] = $Value;
	}
	
	//! Sets the type of the mail
	/*!
	 * \param $Type The new Type.
	 * 
	 * Sets the type of the mail.
	 * It can be TEXTTYPE or HTMLTYPE. 
	 */
	public function setType($Type) {
		$Type = (int) $Type;
		if( $Type < 0 ) {
			$Substract = 1;
			$Type = -$Type;
		}
		if( !($Type & self::TEXTTYPE) && !($Type & self::HTMLTYPE) ) {
			throw new Exception('InvalidType');
			return;
		}
		$this->Type = ( empty($Substract) ) ? $this->Type | $Type : $this->Type ^ $Type;
	}
	
	//! Checks if this file is in the files list
	/*!
	 * \param $Filename The file name.
	 * \return True if this file is in the attached files list.
	 */
	public function containsFile($Filename) {
		return in_array($Filename, $this->AttFiles);
	}
	
	//! Checks if the file list contains any file.
	/*!
	 * \return True if the file list is not empty.
	 * 
	 * Checks if the file list is not empty.
	 */
	public function containsFiles() {
		return !empty($this->AttFiles);
	}
	
	//! Adds a file to the files list
	/*!
	 * \param $Filename The file name.
	 * 
	 * Adds $Filename to the attached files list.
	 */
	public function addFile($Filename) {
		if( $this->containsFile($Filename) ) {
			throw new Exception('FileAlreadyContained');
		}
		$this->AttFiles[] = $Filename;
	}
	
	//! Removes a file from the files list
	/*!
	 * \param $Filename The file name.
	 * 
	 * Removes $Filename from the attached files list.
	 */
	public function removeFile($Filename) {
		if( ($key = array_search($Filename, $this->AttFiles)) === false ) {
			throw new Exception('FileNotContained');
		}
		unset($this->AttFiles[$key]);
	}
	
	//! Sets the subject of the mail
	/*!
	 * \param $Subject The new subject.
	 */
	public function setSubject($Subject) {
// 		if( !is_string($Subject) ) {
// 			throw new Exception('RequireStringParameter');
// 		}
// 		$this->Subject = '=?UTF-8?Q?'.static::escape($Subject).'?=';// Supports UTF-8 and Quote printable encoding
		// If subject is too long, QP returns a bad string, it's working with b64.
		$this->Subject = '=?utf-8?b?'.base64_encode("$Subject").'?=';// Supports UTF-8
// 		log_debug("Convert utf8 subject from {$Subject} to {$this->Subject}");
	}
	
	//! Sets the text body of the mail
	/*!
	 * \param $Body The new body.
	 */
	public function setTEXTBody($Body) {
		if( !is_string($Body) ) {
			throw new Exception('RequireStringParameter');
		}
		$this->TEXTBody = $Body;
	}

	//! Sets the html body of the mail
	/*!
	 * \param $Body The new body.
	 */
	public function setHTMLBody($Body) {
		if( !is_string($Body) ) {
			throw new Exception('RequireStringParameter');
		}
		$this->HTMLBody = static::escape($Body);// Supports UTF-8 and Quote printable encoding
	}
	
	//! Sets the mail content
	/*!
	 * \param $Text The new text for the mail contents.
	 * 
	 * Fills Text and HTML bodies from the given text
	 */
	public function setText($Text) {
		if( !is_string($Text) ) {
			throw new Exception('RequireStringParameter');
		}
		$this->setTEXTBody(strip_tags($Text));
		$this->setHTMLBody(nl2br($Text));
	}
	
	//! Sets the alternative body of the mail
	/*!
	 * \param $Body The new body.
	 */
	public function setAltBody($Body) {
		if( !is_string($Subject) ) {
			throw new Exception('RequireStringParameter');
		}
		$this->AltBody = $Body;
	}
	
	//! Sends the mail to the given address
	/*!
	 * \param $ToAddress The email adress to send this mail
	 * 
	 * Sends the mail to the given adress.
	 * You can pass an array of address to send it to multiple recipients.
	 */
	public function send($ToAddress) {
		if( empty($ToAddress) || (!self::is_email($ToAddress) && !is_array($ToAddress)) ) {
			throw new Exception('InvalidEmailAddress');
		}
		
		if( $this->isMultiContent() ) {
			$Boundary = $this->getBoundary();
			$this->setHeader('MIME-Version', '1.0');
			$this->setHeader('Content-Type', "multipart/alternative; boundary=\"{$Boundary}\"");
			$Body = '';
			$ContentsArr = array();
			if( $this->isAlternative() ) {
				$ContentsArr[] = array(
					'headers' => array(
						'Content-Type' => 'multipart/alternative',
					),
					'body' => ( mb_detect_encoding($this->AltBody, "UTF-8") == "UTF-8" ) ? utf8_decode($this->AltBody) : $this->AltBody,
				);
			}
			
			if( $this->isTEXT() ) {
				$ContentsArr[] = array(
					'headers' => array(
						'Content-Type' => 'text/plain; charset="UTF-8"',
						'Content-Transfer-Encoding' => 'quoted-printable',
					),
					'body' => $this->TEXTBody,
				);
			}
			
			if( $this->isHTML() ) {
				$ContentsArr[] = array(
					'headers' => array(
						'Content-Type' => 'text/html; charset="UTF-8"',
						'Content-Transfer-Encoding' => 'quoted-printable',
					),
					'body' => <<<EOF
<html>
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
</head>
<body>
{$this->HTMLBody}
</body>
</html>
EOF
				);
			}
			
			if( $this->containsFiles() ) {
				$this->setHeader('Content-Type', "multipart/mixed; boundary=\"{$Boundary}\"");
				
				//With files, mail content is overloaded, also we make a blocklist under a bloc with own boundary.
				$subContentsArr = $ContentsArr;
				if( !empty($subContentsArr) ) {
					$ContentsArr = array();
					$subBoundary = $this->getBoundary(1);
					$subBody = '';
					
					foreach($subContentsArr as $Content) {
						$subHeaders = '';
						$Content['headers']['Content-Type'] .= '; format=flowed';
						foreach( $Content['headers'] as $headerName => $headerValue ) {
							$subHeaders .= "{$headerName}: {$headerValue}\n";
						}
						$subBody .= <<<BODY
--{$subBoundary}
{$subHeaders}
{$Content['body']}


BODY;
					}
					$subBody .= <<<BODY
--{$subBoundary}--
BODY;
					$ContentsArr[] = array(
						'headers' => array(
							'Content-Type' => "multipart/alternative; boundary=\"{$subBoundary}\"",
						),
						'body' => $subBody,
					);
					
				}
				
				foreach( $this->AttFiles as $fileName ) {
					if( !is_readable($fileName) ) {
						continue;
					}
					$ContentsArr[] = array(
						'headers' => array(
							'Content-Type' => self::getMimeType($fileName).'; name="'.pathinfo($fileName, PATHINFO_BASENAME).'"',
							'Content-Transfer-Encoding' => 'base64',
							'Content-Disposition' => 'attachment; filename="'.pathinfo($fileName, PATHINFO_BASENAME).'"',
						),
						'body' => chunk_split(base64_encode(file_get_contents($fileName))),
					);
				}
			}
			if( !empty($ContentsArr) ) {
				$Body = '';
				
				foreach($ContentsArr as $Content) {
					$ContentHeaders = '';
					
					if( empty($Content['headers']) ) {
						throw new Exception('ContentRequireHeaders');
					}
					if( empty($Content['body']) ) {
						throw new Exception('ContentRequireBody');
					}
					foreach( $Content['headers'] as $headerName => $headerValue ) {
						$ContentHeaders .= "{$headerName}: {$headerValue}\n";
					}
					$Body .= <<<BODY
--{$Boundary}
{$ContentHeaders}
{$Content['body']}


BODY;
				}
				$Body .= <<<BODY
--{$Boundary}--
BODY;
			}
			
		} else {
			if( $this->isHTML() ) {
				$this->setHeader('MIME-Version', '1.0');
				$this->setHeader('Content-Type', 'text/html; charset="UTF-8"');
				$Body = $this->HTMLBody;
			
			} else if( $this->isTEXT() ) {
				$this->setHeader('MIME-Version', '');
				$this->setHeader('Content-Type', 'text/plain; charset="UTF-8"');
				$Body = $this->TEXTBody;
			
			}
			
		}
		if( empty($Body) ) {
			throw new Exception('EmptyMail');
		}
		
		$Headers = '';
		foreach($this->Headers as $headerName => $headerValue ) {
			if( !empty($headerValue) ) {
				$Headers .= "{$headerName}: {$headerValue}\r\n";
			}
		}
		$Headers .= "\r\n";
		if( !is_array($ToAddress) ) {
			if( !mail($ToAddress, $this->Subject, $Body, $Headers) ) {
				throw new Exception("ProblemSendingMail");
			}
		} else {
			foreach($ToAddress as $MailToData) {
				$MailToEmail = '';
				if( self::is_email($MailToData) ) {
					$MailToEmail = $MailToData;
					
				//More compatibilities with array of data.
				} else if( is_array($MailToData) ) {
					if( !empty($MailToData['mail']) && self::is_email($MailToData['mail']) ) {
						$MailToEmail = $MailToData['mail'];
					} elseif( !empty($MailToData['email']) && self::is_email($MailToData['email']) ) {
						$MailToEmail = $MailToData['email'];
					}
				}
				if( empty($MailToEmail) ) {
					throw new Exception("EmptyEmailAddress");
				}
				if( !mail($MailToEmail, $this->Subject, $Body, $Headers)) {
					throw new Exception("ProblemSendingMail");
				}
			}
		}
		return true;
	}
	
	//! Sets the ReplyTo value of the mail
	/*!
	 * \param $Email The email adress to send this mail
	 */
	public function setReplyTo($Email) {
		$this->setHeader('Return-Path', $Email);
		$this->setHeader('Reply-To', $Email);
	}
	
	//! Sets the Sender value of the mail
	/*!
	 * \param $Email The email adress to send this mail
	 * 
	 * Sets the Sender value of the mail.
	 * This function also sets the ReplyTo value if undefined.
	 */
	public function setSender($Email) {
		$this->setHeader('From', $Email);
		$this->setHeader('Sender', $Email);
		if( empty($Headers['Return-Path']) ) {
			$this->setReplyTo($Email);
		}
	}
	
	//! Gets a boundary
	/*!
	 * \param $BoundaryInd The index of the boundary to get. Default value is 0.
	 * \return The value of the boundary.
	 */
	public function getBoundary($BoundaryInd=0) {
		if( empty($this->MIMEBoundary[$BoundaryInd]) ) {
			$this->MIMEBoundary[$BoundaryInd] = '-=%ORPHEUS_'.md5(microtime(1)+$BoundaryInd).'%=-';
		}
		return $this->MIMEBoundary[$BoundaryInd];
	}
	
	//! Checks if this mail is a HTML mail
	/*!
	 * \return True if this object has a HTML message.
	 */
	public function isHTML() {
		return !empty($this->HTMLBody);
	}
	
	//! Checks if this mail is a TEXT mail
	/*!
	 * \return True if this object has a TEXT message.
	 */
	public function isTEXT() {
		return !empty($this->TEXTBody);
	}
	
	//! Checks if this mail is an alternative mail
	/*!
	 * \return True if this object has an alternative message.
	 */
	public function isAlternative() {
		return !empty($this->AltBody);
	}
	
	//! Checks if this mail contains mutiple contents
	/*!
	 * \return True if this object contains multiple contents.
	 */
	public function isMultiContent() {
		return ( $this->isHTML() + $this->isTEXT() + $this->containsFiles() ) > 1;
	}
	
	//! Checks if the given mail address is valid
	/*!
	 * \param $email The email address.
	 * \return True if this email is valid.
	 */
	public static function is_email($email) {
		return is_email($email);
	}

	//! Gets the mime type of a file.
	/*!
	 * \param $Filename The file name.
	 * \return The mime type of the file.
	 */
	public static function getMimeType($Filename) {
		if( function_exists('finfo_open') ) {
			$finfo = finfo_open(FILEINFO_MIME_TYPE);
			return finfo_file($finfo, $Filename);
		}
		return mime_content_type($Filename);
	}

	//! Escapes the string for mails.
	/*!
	 * \param $string The string to escape.
	 * \return The escaped string for mails.
	 */
	public static function escape($string) {
		//It seems that utf8_encode() is not sufficient, it does not work, but UTF-8 do.
		return quoted_printable_encode(( mb_detect_encoding($string, "UTF-8") == "UTF-8" ) ? $string : utf8_encode($string));
	}
}