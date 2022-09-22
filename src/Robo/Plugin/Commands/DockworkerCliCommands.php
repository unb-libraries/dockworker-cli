<?php

namespace Dockworker\Robo\Plugin\Commands;

use Dockworker\Robo\Plugin\Commands\DockworkerBaseCommands;

/**
 * Defines a base class for all Dockworker-CLI Robo commands.
 */
class DockworkerCliCommands extends DockworkerLocalCommands {

  /**
   * Builds and runs the application's locally, displaying all logs.
   *
   * @param string[] $options
   *   The array of available CLI options.
   *
   * @option $no-build
   *   Do not build the image before running it.
   * @option $no-cache
   *   Do not use any cached steps in the build.
   * @option $no-kill
   *   Do not use kill the container before starting over.
   * @option $no-rm
   *   Do not remove the existing assets before starting over.
   * @option $no-upstream-pull
   *   Do not pull the upstream docker images before building.
   *
   * @command local:build-run
   * @aliases run
   * @throws \Exception
   */
  public function buildRun(array $options = [
    'no-build' => FALSE,
    'no-cache' => FALSE,
    'no-kill' => FALSE,
    'no-rm' => FALSE,
    'no-upstream-pull' => FALSE,
  ]) {
    $this->checkRequiredEnvironmentVariables();
    if (!$options['no-kill']) {
      $this->io()->title("Killing application");
      $this->_exec('docker-compose kill');
    }

    if (!$options['no-rm']) {
      $this->setRunOtherCommand('local:rm');
    }

    if (!$options['no-cache'] && !$options['no-upstream-pull']) {
      $this->setRunOtherCommand('docker:image:pull-upstream');
    }

    if (!$options['no-build']) {
      $build_command = 'local:build';
      if ($options['no-cache']) {
        $build_command = $build_command . ' --no-cache';
      }
      $this->setRunOtherCommand(
        $build_command,
        self::ERROR_BUILDING_IMAGE
      );
    }

    $this->say("Running application...");
    $this->_exec('docker-compose up -d');
    $this->_exec('docker-compose logs -f');
    $this->io()->newLine();
  }

}
