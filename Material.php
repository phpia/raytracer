<?php
abstract class Material {
        abstract protected function scatter(
            Ray $r_in, Hit_record $rec
        ) ;

        abstract protected function get_color_attenuation();

        abstract protected function get_scattered();
};

class Lambertian extends Material {
    public $albedo;
    public $color_attenuation;
    public $scattered;

    function __construct($color){
        $this->albedo = $color;
    }

    function scatter(Ray $r_in, Hit_record $rec){
        $scatter_direction = Vec3::op('+', $rec->normal, random_unit_vector());
        
        // Catch degenerate scatter direction
        if (Vec3::near_zero($scatter_direction))
            $scatter_direction = $rec->normal;

        $this->scattered = new Ray($rec->p, $scatter_direction);
        $this->color_attenuation = $this->albedo;
        return true;
    }

    function get_color_attenuation(){
        return $this->color_attenuation;
    }

    function get_scattered(){
        return $this->scattered;
    }
};

class Metal extends Material {
    
    public $albedo;
    public $color_attenuation;
    public $scattered;  
    public $fuzz;

    function __construct($color, $fuzz){
        $this->albedo = $color;
        $this->fuzz = $fuzz;
    }

    function scatter(Ray $r_in, Hit_record $rec){
        $reflected = Vec3::reflect(Vec3::unit_vector($r_in->direction()), $rec->normal);
        //scattered = ray(rec.p, reflected + fuzz*random_in_unit_sphere());
        $fuzz_r = Vec3::escalar_op('*',random_in_unit_sphere(), $this->fuzz);
        $this->scattered = new Ray($rec->p, Vec3::op('+',$reflected,$fuzz_r));
        $this->color_attenuation = $this->albedo;
        return (Vec3::dot($this->scattered->direction(), $rec->normal) > 0);
    }

    function get_color_attenuation(){
        return $this->color_attenuation;
    }

    function get_scattered(){
        return $this->scattered;
    }
    
};

class Dielectric extends Material {

    public $index_of_refraction;
    public $color_attenuation;
    public $scattered;
    
    function __construct(float $index_of_refraction){
        $this->index_of_refraction = $index_of_refraction;
    }

    function scatter(Ray $r_in, Hit_record $rec){
        $this->color_attenuation = [1.0, 1.0, 1.0];
        $refraction_ratio = $rec->front_face ? (1.0/$this->index_of_refraction) : $this->index_of_refraction;

        $unit_direction = Vec3::unit_vector($r_in->direction());
        /*
        $refracted = Vec3::refract($unit_direction, $rec->normal, $refraction_ratio);
        $this->scattered = new Ray($rec->p, $refracted);
        return true;
        */
        $cos_theta = min(Vec3::dot(Vec3::negative($unit_direction), $rec->normal), 1.0);
        $sin_theta = sqrt(1.0 - $cos_theta*$cos_theta);

        $cannot_refract = $refraction_ratio * $sin_theta > 1.0;            

        //if ($cannot_refract)
        if ($cannot_refract || $this->reflectance($cos_theta, $refraction_ratio) > random_double())
            $direction = Vec3::reflect($unit_direction, $rec->normal);
        else
            $direction = Vec3::refract($unit_direction, $rec->normal, $refraction_ratio);

        $this->scattered = new Ray($rec->p, $direction);
        return true;
    }
    private function reflectance($cosine, $ref_idx) {
        // Use Schlick's approximation for reflectance.
        $r0 = (1-$ref_idx) / (1+$ref_idx);
        $r0 = $r0*$r0;
        return $r0 + (1-$r0)*pow((1 - $cosine),5);
    }

    function get_color_attenuation(){
        return $this->color_attenuation;
    }

    function get_scattered(){
        return $this->scattered;
    }
};