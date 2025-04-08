<?php

namespace DLRoute\Interfaces;

interface InstanceInterface {

    /**
     * Devuelve una instance de clase
     *
     * @return self
     */
    public static function get_instance(): self;
}