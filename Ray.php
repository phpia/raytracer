<?php
class Ray {

    protected $origin;
    protected $direction;

    public function __construct(array $origin, array $direction) {
        $this->origin = $origin;
        $this->direction = $direction;
    }
    
    public function origin(){
        return $this->origin;
    }

    public function direction(){
        return $this->direction;
    }

    public function at(float $t){
        $vec = Vec3::escalar_op('*',$this->direction,$t);
        $vec = Vec3::op('+', $vec, $this->origin);
        return $vec;
    }
}