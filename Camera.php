<?php
class Camera {

    protected $origin;
    protected $horizontal;
    protected $vertical;
    protected $lower_left_corner;


    public function __construct(array $lookfrom, array $lookat, array $vup, float $vfov, float $aspect_ratio) {
        $theta = deg2rad($vfov);
        $h = tan($theta/2);
        $viewport_height = 2.0 * $h;
        $viewport_width = $aspect_ratio * $viewport_height;


        $w = Vec3::unit_vector(Vec3::op('-',$lookfrom, $lookat));
        $u = Vec3::unit_vector(Vec3::cross($vup, $w));
        $v = Vec3::cross($w, $u);

        $this->origin = $lookfrom;
        $this->horizontal = Vec3::escalar_op('*', $u, $viewport_width);
        $this->vertical = Vec3::escalar_op('*', $v, $viewport_height);
        
        //lower_left_corner = origin - horizontal/2 - vertical/2 - w;        
        $this->lower_left_corner = Vec3::op('-', $this->origin, Vec3::escalar_op('/',$this->horizontal,2)); 
        $this->lower_left_corner = Vec3::op('-', $this->lower_left_corner, Vec3::escalar_op('/',$this->vertical,2));
        $this->lower_left_corner = Vec3::op('-', $this->lower_left_corner, $w);
    }
    

    function get_ray($u, $v):Ray {
        $vector = Vec3::op('+',$this->lower_left_corner, Vec3::escalar_op('*',$this->horizontal, $u));
        $vector = Vec3::op('+', $vector, Vec3::escalar_op('*',$this->vertical,$v));
        $vector = Vec3::op('-', $vector, $this->origin);

        $r = new Ray($this->origin, $vector);
        return $r;
    }

    
};