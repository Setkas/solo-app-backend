<?php

namespace Commons\Mailer;

use Dwoo\Compilation\Exception;
use Dwoo\Core;
use PHPMailer;

class mailer {
  /**
   * Template files locations
   *
   * @var string
   */
  private $templatePath = "./src/templates/";

  /**
   * Template files extensions
   *
   * @var string
   */
  private $templateExt = ".tpl";

  /**
   * Sender settings
   *
   * @var array
   */
  private $sender = [
    "email" => "info@carum.cz",
    "name" => "Carum"
  ];

  /**
   * Template engine
   *
   * @var Core|null
   */
  private static $TemplateEngine = null;

  /**
   * constructor that loads template engine
   */
  public function __construct() {
    if (self::$TemplateEngine === null) {
      self::$TemplateEngine = new Core();
    }
  }

  /**
   * Send email from template
   *
   * @param $to
   * @param $template
   * @param $subject
   * @param array $variables
   * @return bool
   */
  public function sendMail($to, $template, $subject, array $variables = []) {
    //Generate template
    try {
      $body = self::$TemplateEngine->get($this->templatePath . $template . $this->templateExt, $variables);
    } catch (Exception $e) {
      return false;
    }

    //Set mailer options
    $mail = new PHPMailer();
    $mail->isHTML(true);

    //Set sender
    $mail->setFrom($this->sender["email"], $this->sender["name"]);
    $mail->addReplyTo($this->sender["email"], $this->sender["name"]);
    //$mail->addBCC($this->sender["email"], $this->sender["name"]);

    //Set receiver
    if (is_array($to)) {
      $mail->addAddress($to["email"], $to["name"]);
    } else {
      $mail->addAddress($to);
    }

    //Set content
    $mail->Subject = $subject;
    $mail->Body = $body;

    //Send email
    return ($mail->send() !== false);
  }

  /**
   * Send email with additional file
   *
   * @param $to
   * @param $file
   * @param $subject
   * @param string $body
   * @return bool
   */
  public function sendFile($to, $file, $subject, $body = "") {
    //Set mailer options
    $mail = new PHPMailer();
    $mail->isHTML(false);

    //Set sender
    $mail->setFrom($this->sender["email"], $this->sender["name"]);
    $mail->addReplyTo($this->sender["email"], $this->sender["name"]);
    //$mail->addBCC($this->sender["email"], $this->sender["name"]);

    //Set receiver
    if (is_array($to)) {
      $mail->addAddress($to["email"], $to["name"]);
    } else {
      $mail->addAddress($to);
    }

    //Add file attachment
    $mail->addAttachment($file, basename($file));

    //Set content
    $mail->Subject = $subject;
    $mail->Body = $body;

    //Send email
    return ($mail->send() !== false);
  }
}
