<?php //class for sending emails - works with phpmailer - also requires the form processor
class SendMail
{

function SendEmail($Body,$Address,$Subject,$From,$Bcc)
{
$Universal= new uni();
require_once("includes/class.phpmailer.php");

$CompanyDetails = $Universal->RetrieveResults('mbd_companyinfo','*',"WHERE id = '1'",'id','asc');
$CompanyDetails = $CompanyDetails['SearchResults'][0];

$width="600";		
$LogoSize = getimagesize("../". $CompanyDetails['logo']);
$body="<div>	
			<table width='". $width ."'>
				<tr>
					<td>
						<a href='". $CompanyDetails['website'] ."'>
							<img border='0' src='". $CompanyDetails['website'] ."/". $CompanyDetails['logo'] ."' width=". $LogoSize[0] ."px height=". $LogoSize[1] ."px alt='". $CompanyDetails['CompanyName'] ."' title='". $CompanyDetails['CompanyName'] ."'>
						</a>
					</td>
				</tr>
			</table>
			<table cellpadding='0' cellspacing='0' width='". $width ."'>
				<tr>
					<td style='padding-top:25px; vertical-align:top; padding-left:10px;'>
						". $Body ."
					</td>
				</tr>
			</table>
		</div>";
	# Mail
$mail=new PHPMailer();
if($From ==""){$mail->From='website@'. $CompanyDetails['domain'];}else{$mail->From=$From;}
$mail->FromName=$CompanyDetails['CompanyName'];
$mail->AddAddress($Address);
if($Bcc !="")
	{
	foreach ($Bcc as $Value)
		{$mail->AddBCC($Value);}
	}
$mail->WordWrap=50;
$mail->IsHTML(true);
$mail->Subject=$Subject;
$mail->Body=$body;
$mail->AltBody=strip_tags($body);
if(!$mail->Send())
	{
	echo "Message could not be sent. <p>";
	echo "Mailer Error: " . $mail->ErrorInfo;
	exit;
	}
}

function EmailForm($Variables,$Session,$newloc,$Button)
{
$Universal= new uni();
$CompanyDetails = $Universal->RetrieveResults('mbd_companyinfo','*',"WHERE id = '1'",'id','asc');
$CompanyDetails = $CompanyDetails['SearchResults'][0];

$Forms = new FormProcessor;

$this->NewLocation = $newloc;
$Forms->Variables = $Variables;
$Forms->SessionName = $Session;
$this->indentfy = $indentfy;

$output = ($_SESSION[$this->SessionName]["Control"] == "Complete" ? "
				<div style='padding-top:5px; padding-bottom:5px;'>
					<span class='heading4'>Thanks </span>for your correspondance, we will be in touch shortly.
				</div>" : "");
if (!isset($_POST['formname_'. $Session])) //Nothing posetd so everything is reset to start a new update
	{
	$_SESSION[$this->SessionName] = NULL; //The session containing values and control is reset
	$Forms->GetDefaults(); // preseting teh values in the form
	$Forms->RenewValues(); // The variables class is then filled with the current values
	$output .= $Forms->DisplayForm3($Forms->Variables,'all','',$Button); // The form containing the current values is the outputed
	}
else //The input containing the session name has been sent so the form begins the update process
	{
	$Forms->RenewValues();
	$Details = $Forms->GetVariables2($Forms->Variables,$Forms->SessionName);
	$Forms->RenewValues();
	//Gets the column names and new values, and creates the update string
	$EmailBody .="Contact form submission:
				  <table>";
	foreach ($Forms->Variables as $Value)
		{
		$EmailBody .= ($Value['Value'] !="" ? "<tr><td>". $Value['Label'] ."</td><td>". $Value['Value'] ."</td></tr>" : "");
		}
	$EmailBody .="</table>";
	if($Details[1] == "yes")
		{
		if($_SESSION[$this->SessionName]["Control"] != "Complete")
			{
			$this->SendEmail($EmailBody,$CompanyDetails['ContactsEmail'] ,"Website Contact Form","","");
			$_SESSION[$this->SessionName]["Control"] = "Complete";
			}
		header('Location: '. $this->NewLocation);
		exit;
		}
	else
		{
		$Forms->RenewValues();
		$output .= "<div class='error_text'>The fields marked have not been filled:</div>". $Forms->DisplayForm3($Forms->Variables,'all','',$Button); 
		}
	}
return $output;
}

}
?>