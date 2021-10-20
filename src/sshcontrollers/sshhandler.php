<?php

namespace ROOT\sshcontrollers;

use Exception;

/* 
** SSH class creator

    @param $host: host for connecton
    @param $port: port for connection, default 22.
    

*/

class SSHHandler{

    //Default port

    private $ssh_port = '22';
    private $ssh_host;
    private $ssh_user;
    private $local_port;
    private $remote_host;
    private $remote_port;
    private $connectionString;
    private $pid;

    function __construct(string $ssh_host, string $ssh_user, string $local_port, string $remote_host, string $remote_port, string $connectionString, string $ssh_port = '22'){

        // $cmd = "nohup ssh ${connectionString} ${user}@${host} > /dev/null 2>&1 & echo $!";

       // $this->$pid = (int) shell_exec("nohup ssh ${connectionString} ${user}@${host} > /dev/null 2>&1 & echo $!");  
      /*  $this->$pid = (int) shell_exec($cmd);  

        if(!$this->$pid){
            throw new Exception('ssh command failed');
        } */

    if($ssh_port != '22'){
        $this->ssh_port = $ssh_port;
    }

    $this->ssh_host = $ssh_host;
    $this->ssh_user = $ssh_user;

    $this->local_port = $local_port;
    $this->remote_host = $remote_host;
    $this->remote_port = $remote_port;
    $this->connectionString = $connectionString;

    }

    public function ssh_bridge(){

    //    $this->pid = (int) shell_exec("nohup ssh ${connectionString} ${user}@${host} > /dev/null 2>&1 & echo $!");  
       if($this->pid){

            shell_exec('sudo killall ssh');
       }

       $cmd = "nohup ssh -vvv -nNT -L $this->local_port:$this->remote_host:$this->remote_port -i $this->connectionString $this->ssh_user@$this->ssh_host > /dev/null 2>&1 & echo $!";

    //    $cmd = "nohup ssh -vvv -nNT -L $this->local_port:$this->remote_host:$this->remote_port -i $this->connectionString $this->ssh_user@$this->ssh_host > /dev/null 2>&1";
       $this->pid = (int) shell_exec($cmd);  
       
       
       if(!$this->pid){

        throw new \Exception('SSH command failed');
    }
       

    }

    function get_ssh_pid(){
        return $this->pid;
    }

    function ssh_bridge_close(){
        $pid = $this->pid;
        `kill $pid > /dev/null 2>&1 & echo $!`;
    }

    function __destruct(){

        shell_exec('sudo killall ssh');
    }

    }
    ?>