<?php

namespace Dockworker\Robo\Plugin\Commands;

use Dockworker\Robo\Plugin\Commands\DockworkerBaseCommands;

/**
 * Defines a base class for all Dockworker-CLI Robo commands.
 */
class DockworkerCliCommands extends DockworkerLocalCommands {

  /**
   * Stops this application's local run, deletes any persistent data, rebuilds its image, and reruns it.
   *
   * @param string[] $options
   *   The array of available CLI options.
   *
   * @option $no-cache
   *   Do not use any cached steps in the build.
   * @option $no-kill
   *   Do not use kill the container before starting over.
   * @option $no-rm
   *   Do not remove the existing assets before starting over.
   *
   * @command local:build-run
   * @aliases start-over
   * @throws \Exception
   */
  public function startOver(array $options = ['no-cache' => FALSE, 'no-kill' => FALSE, 'no-rm' => FALSE]) {
    if (!$options['no-kill']) {
      $this->io()->title("Killing application");
      $this->_exec('docker-compose kill');
    }

    if (!$options['no-rm']) {
      $this->setRunOtherCommand('local:rm');
    }

    $start_command = 'local:build-run';
    if ($options['no-cache']) {
      $start_command = $start_command . ' --no-cache';
    }
    $this->setRunOtherCommand($start_command);
  }

  /**
   * Builds and runs this application's locally, and displays its logs.
   *
   * @param string[] $options
   *   The array of available CLI options.
   *
   * @option $no-cache
   *   Do not use any cached steps in the build.
   * @option $no-upstream-pull
   *   Do not pull the upstream docker images before building.
   * @option $no-build
   *   Do not build any images before starting.
   *
   * @command local:build-run
   * @aliases run
   * @throws \Exception
   */
  public function buildRun(array $options = ['no-cache' => FALSE, 'no-upstream-pull' => FALSE, 'no-build' => FALSE]) {
    $this->checkRequiredEnvironmentVariables();
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
