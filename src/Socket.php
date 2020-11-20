<?php

declare(strict_types=1);

namespace Bloatless\WebSocket;

class Socket
{
    /**
     * @var resource $master Holds the master socket
     */
    protected $master;

    /**
     * @var array Holds all connected sockets
     */
    protected $allsockets = [];

    /**
     * @var resource $context
     */
    protected $context = null;

    protected $protocol = null;
    protected $host = null;
    protected $port = null;

    public function __construct(string $host = 'localhost', int $port = 8000, string $protocol = 'tcp')
    {
    	$this->setStreamContext();

    	$this->protocol = $protocol;
		$this->host = $host;
		$this->port = $port;
    }

	public function setStreamContext(array $options = [], array $params = []): void
	{
		$this->context = stream_context_create($options, $params);
	}

    /**
     * Create a socket on given host/port
     *
     * @throws \RuntimeException
     * @return void
     */
    public function createSocket(): void
    {
		ob_implicit_flush(1);

        $url = $this->protocol . '://' . $this->host . ':' . $this->port;

        $this->master = stream_socket_server(
            $url,
            $errno,
            $err,
            STREAM_SERVER_BIND | STREAM_SERVER_LISTEN,
            $this->context
        );

        if ($this->master === false) {
            throw new \RuntimeException('Error creating socket: ' . $err);
        }

        $this->allsockets[] = $this->master;
    }

    /**
     * Reads from stream.
     *
     * @param $resource
     * @throws \RuntimeException
     * @return string
     */
    protected function readBuffer($resource): string
    {
        $buffer = '';
        $buffsize = 8192;
        $metadata['unread_bytes'] = 0;
        do {
            if (feof($resource)) {
                throw new \RuntimeException('Could not read from stream.');
            }
            $result = fread($resource, $buffsize);
            if ($result === false || feof($resource)) {
                throw new \RuntimeException('Could not read from stream.');
            }
            $buffer .= $result;
            $metadata = stream_get_meta_data($resource);
            $buffsize = ($metadata['unread_bytes'] > $buffsize) ? $buffsize : $metadata['unread_bytes'];
        } while ($metadata['unread_bytes'] > 0);

        return $buffer;
    }

    /**
     * Write to stream.
     *
     * @param $resource
     * @param string $string
     * @return int
     */
    public function writeBuffer($resource, string $string): int
    {
        $stringLength = strlen($string);
        if ($stringLength === 0) {
            return 0;
        }

        for ($written = 0; $written < $stringLength; $written += $fwrite) {
            $fwrite = @fwrite($resource, substr($string, $written));
            if ($fwrite === false) {
                throw new \RuntimeException('Could not write to stream.');
            }
            if ($fwrite === 0) {
                throw new \RuntimeException('Could not write to stream.');
            }
        }

        return $written;
    }
}
