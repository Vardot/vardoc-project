<?php
/**
 * @file
 * Contains \Vardot\Varbase\Procedures.
 */

namespace Vardot\Varbase;
use Composer\Script\Event;
use Composer\Package\PackageInterface;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Yaml\Yaml;

/**
 * Varbase build default procedures and script handler.
 */
class Procedures {

  /**
   * Get the Drupal root directory.
   *
   * @param type $project_root
   * @return type
   */
  protected static function getDrupalRoot($project_root) {
    return $project_root . '/docroot';
  }
  
  /**
   * Get a list of Varbase features.
   *
   * @return array
   */
  protected static function getVarbaseFeatures() {
    return array(
      'varbase_admin',
      'varbase_core',
      'varbase_development',
      'varbase_internationalization',
      'varbase_landing',
      'varbase_media',
      'varbase_page',
      'varbase_security',
      'varbase_seo',
      'varbase_site',
      'varbase_user',
      'varbase_webform',
    );
  }
 
  /**
   * Post Drupal Scaffold Procedure.
   *
   * @param \Composer\EventDispatcher\Event $event
   *   The script event.
   */
  public static function postDrupalScaffoldProcedure(\Composer\EventDispatcher\Event $event) {
    
    $fs = new Filesystem();
    $root = static::getDrupalRoot(getcwd());

    if ($fs->exists($root . '/profiles/varbase/src/assets/robots-staging.txt')) {
      // Create staging robots file.
      copy($root . '/profiles/varbase/src/assets/robots-staging.txt', $root . '/robots-staging.txt');
    }
    
    if ($fs->exists($root . '/.htaccess')
      && $fs->exists($root . '/profiles/varbase/src/assets/htaccess_extra')) {

      // Alter .htaccess file.
      $htaccess_path = $root . '/.htaccess';
      $htaccess_lines = file($htaccess_path);
      $lines = [];
      foreach ($htaccess_lines as $line) {
        $lines[] = $line;
        if (strpos($line, "RewriteEngine on") !== FALSE) {
          $lines = array_merge($lines, file($root . '/profiles/varbase/src/assets/htaccess_extra'));
        }
      }
      file_put_contents($htaccess_path, $lines);
    }

    if ($fs->exists($root . '/profiles/varbase/src/assets/development.services.yml')) {
      // Alter development.services.yml to have Varbase's Local development services.
      copy($root . '/profiles/varbase/src/assets/development.services.yml', $root . '/sites/development.services.yml');
    }

    // Copy ACE librarary into /modules/contrib/ace_editor/libraries.
    if ($fs->exists($root . '/libraries/ace/src-min-noconflict/ace.js')) {
      mkdir($root . '/modules/contrib/ace_editor/libraries', 0777, true);
      rename($root . '/libraries/ace', $root . '/modules/contrib/ace_editor/libraries/ace');
    }

  }
  
  /**
   * Post Drupal Scaffold Sub Profile Procedure.
   *
   *  Remove the distribution item for the parent varbase profile, as we will
   *  use this sub profile as the distribution cover on the install step.
   *  
   *  This should be used in the composer.json file of a Sub Profile of Varbase
   *
   *  For Example:
   *  -------------------------------------------------------------------------
   *    "post-drupal-scaffold-cmd": [
   *      "Vardot\\Varbase\\Procedures::postDrupalScaffoldProcedure",
   *      "Vardot\\Varbase\\Procedures::postDrupalScaffoldSubProfileProcedure"
   *    ],
   *  -------------------------------------------------------------------------
   * 
   * @param \Composer\EventDispatcher\Event $event
   *   The script event.
   */
  public static function postDrupalScaffoldSubProfileProcedure(\Composer\EventDispatcher\Event $event) {

    $fs = new Filesystem();
    $root = static::getDrupalRoot(getcwd());

    // File name for the varbase.info.yml file.
    $varbase_info_file = '/profiles/varbase/varbase.info.yml';
    $varbase_info_file_with_root_path = $root . $varbase_info_file;

    if ($fs->exists($varbase_info_file_with_root_path)) {
      // Parse the varbase.info.yml file.
      $varbase_info = Yaml::parse(file_get_contents($varbase_info_file_with_root_path));
      
      /**
       *  Remove the distribution item for the parent varbase profile, as we will
       *  use this sub proifle as the distribution cover on the install step.
       */
      if (isset($varbase_info['distribution'])) {
        unset($varbase_info['distribution']);
      }
      
      // Dump the array to string of Yaml format.
      $new_varbase_info = Yaml::dump($varbase_info);

      // Save the new varbase info into the varbase info file.
      file_put_contents($varbase_info_file_with_root_path, $new_varbase_info);
    }
  }

  /**
   * Post install procedure.
   *
   * @param \Composer\Script\Event $event
   *   The script event.
   */
  public static function postInstallProcedure(\Composer\Installer\PackageEvent $event) {
    if ($event->getOperation()->getPackage()->getName() == "vardot/varbase") {
      // Generated the new varbase info into the varbase info file.
      Procedures::updateProfileInfoFiles($event);
    }
  }
  
  /**
   * Post update procedure.
   *
   * @param \Composer\Script\Event $event
   *   The script event.
   */
  public static function postUpdateProcedure(\Composer\Installer\PackageEvent $event) {
    if ($event->getOperation()->getPackage()->getName() == "vardot/varbase") {
      // Generated the new varbase info into the varbase info file.
      Procedures::updateProfileInfoFiles($event);
    }
  }

  /**
   * Update Profile Info File.
   *
   * @param Event $event
   */
  public static function updateProfileInfoFiles(\Composer\Installer\PackageEvent $event) {

    $fs = new Filesystem();
    $root = static::getDrupalRoot(getcwd());

    // File name for the varbase.info.yml file.
    $varbase_info_file = '/profiles/varbase/varbase.info.yml';
    $varbase_info_file_with_root_path = $root . $varbase_info_file;

    if ($fs->exists($varbase_info_file_with_root_path)) {
      $varbase_info_datestamp = time();
      // Parse the varbase.info.yml file.
      $varbase_info = Yaml::parse(file_get_contents($varbase_info_file_with_root_path));

      // Varbase version.
      $varbase_version = '8.x-4.x-dev';
      $varbase_package = $event->getComposer()
        ->getRepositoryManager()
        ->getLocalRepository()
        ->findPackage('vardot/varbase', "*");

      // Only get the version if it was not DEV. to follow Drupal standard.
      if (!$varbase_package->isDev()) {
        $varbase_version = $varbase_package->getVersion();
      }

      // Information added by varbase-build packaging script.
      $varbase_info['version'] = $varbase_version;
      $varbase_info['project'] = 'varbase';
      $varbase_info['datestamp'] = $varbase_info_datestamp;

      // Dump the array to string of Yaml format.
      $new_varbase_info = Yaml::dump($varbase_info);

      // Save the new varbase info into the varbase info file.
      file_put_contents($varbase_info_file_with_root_path, $new_varbase_info);

      // Print out a message to the user on Install and update.
      $event->getIO()->write(" Information added by varbase-build packaging script.");
      $event->getIO()->write(" - version: " . $varbase_version);
      $event->getIO()->write(" - datestamp: " . $varbase_info_datestamp);
      $event->getIO()->write(" - Updated files.");
      $event->getIO()->write($varbase_info_file);
      
      // A list of Varbase features.
      $varbase_features = static::getVarbasefeatures();

      // Update all varbase features info.yml files.
      foreach ($varbase_features as $varbase_feature) {
        $varbase_feature_info_file = '/profiles/varbase/modules/varbase_features/' . $varbase_feature . '/' . $varbase_feature . '.info.yml';
        $varbase_feature_info_file_with_root_path = $root . $varbase_feature_info_file; 
        if ($fs->exists($varbase_feature_info_file_with_root_path)) {
          // Parse the varbase feature info.yml file.
          $varbase_feature_info = Yaml::parse(file_get_contents($varbase_feature_info_file_with_root_path));

          // Information added by varbase-build packaging script.
          $varbase_feature_info['version'] = $varbase_version;
          $varbase_feature_info['datestamp'] = $varbase_info_datestamp;

          // Dump the array to string of Yaml format.
          $new_varbase_feature_info = Yaml::dump($varbase_feature_info);

          // Save the new varbase feature info into the info.yml file.
          file_put_contents($varbase_feature_info_file_with_root_path, $new_varbase_feature_info);

          // Print out a message to the user on Install and update.
          $event->getIO()->write($varbase_feature_info_file);
        }
      }
    }
  }
}
