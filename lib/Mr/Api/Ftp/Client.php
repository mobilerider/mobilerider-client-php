<?php

namespace Mr\Api\Ftp;

class Client
{
    const OP_GET = 'get';
    const OP_PUT = 'put';
    const OP_RENAME = 'rename';
    const OP_DELETE = 'delete';

    protected $_conn = null;
    protected $_options;
    protected $allowedOperations = array();

    /**
     * Constructor
     *
     * @param string $host      FTP setver hostname
     * @param string $user      FTP auth user
     * @param string $password  FTP auth password
     *
     * @param array $options
     * @constructor
     */
    public function __construct($host, $user, $password, $options = array())
    {
        $this->host = $host;
        $this->user = $user;
        $this->password = $password;
        $this->connect();

        if (!$this->isConnected()) {
            $this->refreshConnection();
        }

        $this->_options = $options;

        $this->loadPermissions();
    }

    public function getConnection()
    {
        return $this->_conn;
    }

    public function checkPermissions($operation, $strict = true)
    {
        if (empty($operation) || !in_array($operation, array(
            self::OP_GET,
            self::OP_PUT,
            self::OP_RENAME,
            self::OP_DELETE
        ))) {
            throw new Exception('Invalid operation');
        }

        if (array_key_exists($operation, $this->allowedOperations) &&
            $this->allowedOperations[$operation]) {
            return true;
        }

        if ($strict) {
            throw new Exception('Operation is not allowed');
        }

        return false;
    }

    public function loadPermissions()
    {
        $this->allowedOperations[self::OP_GET] = (bool) $this->getOption('can_read', true);
        $this->allowedOperations[self::OP_PUT] = (bool) $this->getOption('can_write', true);
        $this->allowedOperations[self::OP_RENAME] = (bool) $this->getOption('can_write', true);
        $this->allowedOperations[self::OP_DELETE] = (bool) $this->getOption('can_delete', true);
    }

    public function setOption($name, $value = true)
    {
        $this->_options[$name] = $value;

        $this->loadPermissions();
    }

    /**
     * Returns an option value by its name.
     *
     * @param $name
     * @param null $default
     * @param bool $required
     * @return null
     * @throws Exception
     */
    public function getOption($name, $default = null, $required = false)
    {
        if (isset($this->_options[$name])) {
            return $this->_options[$name];
        } else if ($required) {
            throw new Exception('Option: ' . $name . ' is required');
        }

        return $default;
    }

    /**
     * Reopens connection to server.
     *
     * @return boolean
     */
    public function refreshConnection($wait = 1) // seconds
    {
        $attempt = 0;
        $this->close();

        while (++$attempt < 6) {
            sleep($wait);
            if ($this->connect()) {
                break;
            }
        }

        return $this->isConnected();
    }

    /**
     * Checks if connection to server is opened
     *
     * @return boolean
     */
    public function isConnected()
    {
        return !empty($this->_conn);
    }

    /**
     * Opens connection to server
     *
     * @return boolean
     */
    public function connect()
    {
        $this->_conn = @ftp_connect($this->host);

        if (!$this->isConnected() || !@ftp_login($this->_conn, $this->user, $this->password)) {
            return false;
        }

        @ftp_pasv($this->_conn, true);

        return !empty($this->_conn);
    }

    /**
     * Downloads file
     *
     * @param string $source Source file path
     * @param string $dest   Destination path
     *
     * @return boolean
     */
    public function download($source, $dest)
    {
        if (!$this->checkPermissions(self::OP_GET)) {
            return false;
        }

        $tries = 2;
        while ($failure = !$this->get($source, $dest) && $tries--) {
            $this->refreshConnection();
        }

        if ($failure) {
            return false;
        } else {

        }

        return true;
    }

    /**
     * Uploads file to server
     *
     * @param string $source Source file path
     * @param string $dest   Destination path
     *
     * @return boolean
     */
    public function upload($source, $dest)
    {
        if (!$this->checkPermissions(self::OP_PUT)) {
            return false;
        }

        $source = $this->normalizePath($source);
        $dest = $this->normalizePath($dest);

        $tries = 2;
        while ($failure = !$this->createFolder(dirname($dest)) && $tries--) {
            $this->refreshConnection();
        }

        if ($failure) {
            return false;
        }

        $tries = 2;
        while ($failure = !$this->put($source, $dest) && $tries--) {
            $this->refreshConnection();
        }

        return !($failure);
    }

    /**
     * Uploads several files to server.
     *
     * @param array $files Associative array with key value pairs representing from => to
     *
     * @return array $errors
     */
    public function uploadFiles($files)
    {
        if (!$this->checkPermissions(self::OP_PUT)) {
            return false;
        }

        $errors = array();
        $index = 0;

        foreach ($files as $from => $to) {
            try {
                $this->upload($from, $to);
                $index++;
            } catch (Exception $ex) {
                $errors[$index] = $ex->getMessage();
            }
        }

        return $errors;
    }

    /**
     * Renames file on server
     *
     * @param string $oldname Old filename
     * @param string $newname New filename
     *
     * @return boolean
     */
    public function rename($oldname, $newname)
    {
        if (!$this->checkPermissions(self::OP_RENAME)) {
            return false;
        }

        $tries = 2;

        $this->createFolder(dirname($newname));

        while ($failure = !$this->_rename($oldname, $newname) && $tries--) {
            $this->refreshConnection();
        }

        return !($failure);
    }

    /**
     * Checks if file exists on server
     *
     * @param string $filename Filename
     *
     * @return boolean
     */
    public function fileExists($filename)
    {
        return $this->size($filename) != -1;
    }

    public function folderExists($dirname)
    {
        $original = ftp_pwd($this->_conn);

        try {
            $exists = ftp_chdir($this->_conn, $dirname);
        } catch (Exception $ex) {
            $exists = false;
        }

        ftp_chdir($this->_conn, $original);

        return $exists;
    }

    /**
     * Returns file size
     *
     * @param string $filename Filename
     *
     * @return type
     */
    public function getFileSize($filename)
    {
        return $this->size($filename);
    }

    /**
     * Deletes file
     *
     * @param string $source Filename
     *
     * @return boolean
     */
    public function delete($path)
    {
        if (!$this->checkPermissions(self::OP_DELETE)) {
            return false;
        }

        // Avoids deleting any major folder
        if (empty($path) || (substr_count($path, '/') < 4)) {
            throw new Exception("Path is not valid or too ambiguous", 1);
        }

        return ftp_delete($this->_conn, $path);
    }

    public function createSymlink($src, $dst)
    {
        // This seems to work in opposite way to all other
        // symlink commands examles in other platforms
        @ftp_site($this->_conn, 'symlink ' . $dst . ' ' . $src);
    }

    /**
     * Uploads file to server
     *
     * @param string $source Source file path
     * @param string $dest   Destination path
     *
     * @return boolean
     */
    public function put($source, $dest)
    {
        if (!$this->checkPermissions(self::OP_PUT)) {
            return false;
        }

        return ftp_put($this->_conn, $dest, $source, FTP_BINARY);
    }


    /**
     * Downloads file from server
     *
     * @param string $source Source file path
     * @param string $dest   Destination path
     *
     * @return mixed
     */
    public function get($source, $dest)
    {
        if (!$this->checkPermissions(self::OP_GET)) {
            return false;
        }

        return ftp_get($this->_conn, $dest, $source, FTP_BINARY);
    }
    // TODO: make it recursive
    public function listItems($path)
    {
        if (!$this->checkPermissions(self::OP_GET)) {
            return false;
        }

        return ftp_nlist($this->_conn, $path);
    }

    /**
     * Renames file
     *
     * @param string $oldname Old filename
     * @param string $newname New filename
     *
     * @return boolean
     */
    public function _rename($oldname, $newname)
    {
        if (!$this->checkPermissions(self::OP_RENAME)) {
            return false;
        }

        return @ftp_rename($this->_conn, $oldname, $newname);
    }

    /**
     * Returns file size
     *
     * @param string $filename Filename
     *
     * @return int
     */
    public function size($filename)
    {
        if (!$this->checkPermissions(self::OP_GET)) {
            return false;
        }

        return ftp_size($this->_conn, $filename);
    }

    /**
     * Creates folder on server
     *
     * @param string $folder Folder name
     *
     * @return boolean
     */
    public function createFolder($folder)
    {
        // TODO: use separate operation MKDIR for this
        if (!$this->checkPermissions(self::OP_PUT)) {
            return false;
        }

        $chunks = explode('/', $folder);
        $path = '';

        foreach ($chunks as $chunk) {
            if ($chunk == '') {
                continue;
            }
            $path .= '/' . $chunk;
            if (!@ftp_chdir($this->_conn, $path)) {
                if (!@ftp_mkdir($this->_conn, $path)) {
                    return false;
                }
            }
        }

        return true;
    }

    /**
     * Normalize file path
     *
     * @param string $path Path
     *
     * @return string
     */
    public function normalizePath($path)
    {
        return preg_replace('-/+-', '/', $path);
    }

    /**
     * Closes ftp connection
     *
     * @return void
     */
    public function close()
    {
        if ($this->_conn) {
            @ftp_close($this->_conn);
        }

        $this->_conn = null;
    }

}
?>

