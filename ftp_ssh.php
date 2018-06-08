
<?php
public function uploadFTPDirectory($source, $destination, $type, $stream = true)
    {
        ftp_pasv($this->con, true);

        $dir_name = '/resources/';
         
        try {
            set_time_limit(0);
            switch ($type) {
                case 'FTP':
                case 'FTPES':
                    
                    if ($this->passive) {
                        if (!ftp_pasv($this->con, true)) {
                            $this->passive = false;
                            ftp_pasv($this->con, false);
                        }
                    }
                    
                    $d = dir($source);

                    foreach (glob($source."/*") as $filename)
                    {
                        if(is_dir($filename))
                        { 
                            
                            
                            ftp_chdir($this->con, '/'.$destination);
                            ftp_mkdir($this->con, '/'.$destination."/".$dir_name);
                            ftp_chdir($this->con, '/'.$destination."/".$dir_name);
                            $current_dir = ftp_pwd($this->con);
                            
                            print_r($current_dir);
                            print_r("::after t::");

                            foreach (glob('/'.$source."/".$dir_name."/*") as $inner_filename)
                            {
                                print_r($inner_filename);

                                if(!$return_value = ftp_put($this->con, basename($inner_filename) , $inner_filename, FTP_BINARY))
                                {
                                    if(!$return_value = ftp_put($this->con, basename($inner_filename) , $inner_filename, FTP_ASCII))
                                    {
                                        $this->reconnect(false, 10);
                                        $return_value = ftp_put($this->con, basename($inner_filename) , $inner_filename, FTP_BINARY);
                                    }
                                }
                            
                            }
                        }
                        else
                        {
                            if(!$return_value = ftp_put($this->con, basename($filename) , $filename, FTP_BINARY))
                            {
                                if(!$return_value = ftp_put($this->con, basename($filename) , $filename, FTP_ASCII))
                                {
                                    $this->reconnect(false, 10);
                                    $return_value = ftp_put($this->con, basename($filename) , $filename, FTP_BINARY);
                                }
                            }
                        }
                    }

                    
                    break;

                case 'SSH':
                   
                        if ($stream) {

                         if (is_null($this->sftp))
                            $this->sftp = ssh2_sftp($this->con);

                       

                        foreach (glob($source."/*") as $filename)
                        {
                            if(is_dir($filename))
                            {

                                $create_dir = ssh2_sftp_mkdir($this->con, '/'.$destination."/".$dir_name);
                                $sftpStream = @fopen('ssh2.sftp://'.(int)$sftp.'/'.$destination."/".$dir_name, 'w');
                                
                                if (!$sftpStream) {
                                    throw new Exception("Could not open remote file:". '/'.$destination."/".$dir_name);
                                }

                                foreach (glob('/'.$source."/".$dir_name."/*") as $inner_filename)
                                {
                                    

                                    $data_to_send = @file_get_contents($inner_filename);

                                    if ($data_to_send === false) {
                                        throw new Exception("Could not open local file: $inner_filename.");
                                    }

                                    if ($return_value = @fwrite($sftpStream, $data_to_send) === false) {
                                        throw new Exception("Could not send data from file: $inner_filename.");
                                    }

                                    
                                }
                            }
                            else
                            {
                                    $outer_data_to_send = @file_get_contents($filename);

                                    if ($outer_data_to_send === false) {
                                        throw new Exception("Could not open local file: $filename.");
                                    }

                                    if ($return_value = @fwrite($sftpStream, $outer_data_to_send) === false) {
                                        throw new Exception("Could not send data from file: $filename.");
                                    }
                            }
                        }
                       
                        
                        break;
                    }
            }

        } catch (FtpException $e) {
            echo 'Caught exception: ', $e->getMessage(), chr(10), $this->basicInfo();
            return false;
        } catch (ErrorException $e) {
            echo 'Caught exception: ', $e->getMessage(), chr(10), $this->basicInfo();
            return false;
        } catch (Exception $e) {
            echo 'Caught exception: ', $e->getMessage(), chr(10), $this->basicInfo();
            return false;
        }
    }
?>
