<?php

class Swift_Transport_StreamBuffer_SocketTimeoutTest extends \PHPUnit_Framework_TestCase
{
    protected $buffer;

    protected $server;

    public function setUp()
    {
        if (!defined('SWIFT_SMTP_HOST')) {
            $this->markTestSkipped(
                'Cannot run test without an SMTP host to connect to (define '.
                'SWIFT_SMTP_HOST in tests/acceptance.conf.php if you wish to run this test)'
             );
        }

        $serverStarted = false;
        for ($i = 0; $i < 5; ++$i) {
            $this->randomHighPort = rand(50000, 65000);
            $this->server = stream_socket_server('tcp://127.0.0.1:'.$this->randomHighPort);
            if ($this->server) {
                $serverStarted = true;
            }
        }

        $this->buffer = new Swift_Transport_StreamBuffer(
            $this->getMock('Swift_ReplacementFilterFactory')
        );
    }

    protected function initializeBuffer()
    {
        $host = '127.0.0.1';
        $port = $this->randomHighPort;

        $this->buffer->initialize(array(
            'type' => Swift_Transport_IoBuffer::TYPE_SOCKET,
            'host' => $host,
            'port' => $port,
            'protocol' => 'tcp',
            'blocking' => 1,
            'timeout' => 1,
            'stream_context_options' => array(),
            ));
    }

    public function testTimeoutException()
    {
        $this->initializeBuffer();
        $e = null;
        try {
            $line = $this->buffer->readLine(0);
        } catch (Exception $e) {
        }
        $this->assertInstanceof('Swift_IoException', $e, 'IO Exception Not Thrown On Connection Timeout');
        $this->assertRegExp('/Connection to .* Timed Out/', $e->getMessage());
    }

    public function tearDown()
    {
        if ($this->server) {
            stream_socket_shutdown($this->server, STREAM_SHUT_RDWR);
        }
    }
}