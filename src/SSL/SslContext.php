<?php

namespace Frugal\Core\SSL;

readonly class SslContext
{
    public function __construct(
        public string $localCertFile,
        public string $localPrimaryKey,
        public bool $verifyPeer,
        public bool $allowSelfSigned,
        public string $caFile,
        public bool $capturePeerCert,
        public bool $capturePeerCertChain,
        public bool $verifyPeerName
    ) {}

    public function toArray() : array
    {
        return [
            'local_cert' => $this->localCertFile,
            'local_pk'   => $this->localPrimaryKey,
            'verify_peer' => $this->verifyPeer,
            'allow_self_signed' => $this->allowSelfSigned,
            'cafile' => $this->caFile,
            'capture_peer_cert' => $this->capturePeerCert,
            'capture_peer_cert_chain' => $this->capturePeerCertChain,
            'verify_peer_name' => $this->verifyPeerName
        ];
    }
}