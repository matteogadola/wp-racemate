<?php

class RmpiaDeactivator {

  public static function deactivate() {
    flush_rewrite_rules();
  }
}
