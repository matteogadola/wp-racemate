<?php

defined('ABSPATH') || exit;

require_once RMIAP_PLUGIN_PATH . 'vendor/autoload.php';

use CodiceFiscale\Checker;
use CodiceFiscale\Subject;
use CodiceFiscale\Validator;
use CodiceFiscale\InverseCalculator;

class RmiapTin {

  public static function isTinValid($tin) {
    try {
      $validator = new Validator($tin);

      if (!$validator->isFormallyValid()) {
        throw new Exception('Codice fiscale non corretto');
      }

      return true;
    } catch (Exception $e) {
      throw new Exception('Codice fiscale non corretto');
    }
  }

  public static function verifyTin($tin, $first_name, $last_name) {
    try {
      RmiapTin::isTinValid($tin);

      $inverseCalculator = new InverseCalculator($tin);
      $inverseSubject = $inverseCalculator->getSubject();

      $subject = new Subject(array(
        "name"          => $first_name,
        "surname"       => $last_name,
        "birthDate"     => $inverseSubject->getBirthDate()->format('Y-m-d'),
        "gender"        => $inverseSubject->getGender(),
        "belfioreCode"  => $inverseSubject->getBelfioreCode()
      ));

      $checker = new Checker($subject, array(
        "codiceFiscaleToCheck"  => strtoupper($tin),
        "omocodiaLevel"         => Checker::ALL_OMOCODIA_LEVELS
      ));

      if (!$checker->check()) {
        throw new Exception('Il codice fiscale non corrisponde ai dati inseriti');
      }
      
      return array(
        'birth_date'  => $inverseSubject->getBirthDate()->format('Y-m-d'),
        'birth_year'  => $inverseSubject->getBirthDate()->format('Y'),
        'gender'      => $inverseSubject->getGender(),
      );
    } catch (Exception $e) {
      throw new Exception($e->getMessage());
    }
  }
}
