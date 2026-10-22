<?php
class BackgroundRemovalService {
    private $provider;

    public function __construct($config) {
        $this->provider = new PixelcutProvider($config);
    }

    public function process($localImagePath) {
        return $this->provider->removeBackground($localImagePath);
    }
}