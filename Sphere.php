<?php
class Sphere extends Hittable {
    public  $center;
    protected  $radius;
    public $material;
    //public $rec; // Hit_record

   

    public function __construct(array $center, float $radius, Material $material) {
        $this->center = $center;
        $this->radius = $radius;
        $this->material = $material;
    }
    
    public function hit(Ray $r, float $t_min, float $t_max){
        $oc = Vec3::op('-', $r->origin(), $this->center);
        $a = Vec3::length_squared($r->direction());
        $half_b = Vec3::dot($oc, $r->direction());
        $c = Vec3::length_squared($oc) - $this->radius*$this->radius;
        $discriminant = $half_b*$half_b - $a*$c;
        
        if ($discriminant < 0) return false;
        $sqrtd = sqrt($discriminant);
    
        // Find the nearest root that lies in the acceptable range.
        $root = (-$half_b - $sqrtd) / $a;
        if ($root < $t_min || $t_max < $root) {
            $root = (-$half_b + $sqrtd) / $a;
            if ($root < $t_min || $t_max < $root)
                return false;
        }
            
        $t = $root;
        $p = $r->at($root);
        
        //$rec->normal = ($rec->p - $center) / $radius;
        $normal = Vec3::op('-',$p,$this->center);
        $normal = Vec3::escalar_op('/',$normal, $this->radius);
        $rec = new Hit_record($p, $normal, $t);
        $rec->material = $this->material;        
        $rec->set_face_normal($r, $normal);
    
        return $rec;
    }
}