<?php
// Copyright 2004-present Facebook. All Rights Reserved.

// When running arc diff, the user's commit is pushed to the master git
// repository into a branch named refs/autobuilds/<diffid>. Then curl
// is used to initiate a Jenkins poll of the source repo. When the new
// commit is found, it is pulled down to a build slave and built. The
// Jenkins job is configured so that the arcanist/bin/update_build_info.php
// program reads the stdout and updates the phabricator diff page with
// the test results.

final class FacebookBuildServer {

  private function getBuildServer() {
    return "ci-builds.fb.com";
  }

  private function getGitURL() {
    return "ssh://projects.git.vip.facebook.com/data/gitrepos/jcommon.git";
  }

  private function getJobURI() {
    return "http://{$this->getBuildServer()}/git/notifyCommit?".
      "url={$this->getGitURL()}";
  }

  public function startProjectBuilds($async, $diff_id=null) {
    // Push the source up to the master repo so that Jenkins
    // can pull it down and build it
    $gitcmd = "git push origin HEAD:refs/autobuilds/{$diff_id}";
    $git_future = new ExecFuture($gitcmd);
    $git_future->resolvex();

    $url = $this->getJobURI();

    // Initiate a git poll for the mysql jobs in Jenkins
    $cmd = "curl --max-time 5 -s {$url}";

    $future = new ExecFuture($cmd);

    if ($async === true) {
      echo "Launching a build on the Jenkins server...\n";
      $future->resolvex();
    }
  }

}
