<?php
// Camera with adjustable depth-of-field (dof)
class Camera_DOF {

    protected $origin;
    protected $horizontal;
    protected $vertical;
    protected $lower_left_corner;

    protected $u, $v, $w;
    protected $lens_radius;


    public function __construct(array $lookfrom, array $lookat, array $vup, float $vfov, float $aspect_ratio, float $aperture, float $focus_dist) {
        $theta = deg2rad($vfov);
        $h = tan($theta/2);
        $viewport_height = 2.0 * $h;
        $viewport_width = $aspect_ratio * $viewport_height;


        $this->w = Vec3::unit_vector(Vec3::op('-',$lookfrom, $lookat));
        $this->u = Vec3::unit_vector(Vec3::cross($vup, $this->w));
        $this->v = Vec3::cross($this->w, $this->u);

        $this->origin = $lookfrom;
        $this->horizontal = Vec3::escalar_op('*', $this->u, $viewport_width * $focus_dist);
        $this->vertical = Vec3::escalar_op('*', $this->v, $viewport_height * $focus_dist);                

        $this->lens_radius = $aperture / 2;
                
        //lower_left_corner = origin - horizontal/2 - vertical/2 - w;        
        //lower_left_corner = origin - horizontal/2 - vertical/2 - focus_dist*w;
        $this->lower_left_corner = Vec3::op('-', $this->origin, Vec3::escalar_op('/', $this->horizontal, 2)); 
        $this->lower_left_corner = Vec3::op('-', $this->lower_left_corner, Vec3::escalar_op('/', $this->vertical, 2));
        $this->lower_left_corner = Vec3::op('-', $this->lower_left_corner, Vec3::escalar_op('*', $this->w, $focus_dist));
    }
    

    function get_ray($s, $t):Ray {
        $rd = Vec3::escalar_op('*',random_in_unit_disk(),$this->lens_radius);
        $offset_u = Vec3::escalar_op('*',$this->u,$rd[0]); 
        $offset_v = Vec3::escalar_op('*',$this->v,$rd[1]);
        $offset = Vec3::op('+', $offset_u, $offset_v);

        $orig = Vec3::op('+',$this->origin, $offset);

        $direction = Vec3::op('+', $this->lower_left_corner, Vec3::escalar_op('*',$this->horizontal,$s));
        $direction = Vec3::op('+', $direction, Vec3::escalar_op('*',$this->vertical,$t));
        $direction = Vec3::op('-', $direction, $this->origin);
        $direction = Vec3::op('-', $direction, $offset);

        $r = new Ray($orig, $direction);

        return $r;
    }

    
};