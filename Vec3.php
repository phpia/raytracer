<?php

class Vec3 {

    public static function op(string $op, array $a, array $b){
        // comprobar mismo tamaño, dimension 3
        switch($op) {
            case '*': return [$a[0]*$b[0], $a[1]*$b[1], $a[2]*$b[2]];
            case '+': return [$a[0]+$b[0], $a[1]+$b[1], $a[2]+$b[2]];
            case '-': return [$a[0]-$b[0], $a[1]-$b[1], $a[2]-$b[2]];
            case '/': return [$a[0]/$b[0], $a[1]/$b[1], $a[2]/$b[2]];
        }
    }

    public static function escalar_op(string $op, array $a, float $escalar): array{
        switch($op) {
            case '*': return [$a[0]*$escalar, $a[1]*$escalar, $a[2]*$escalar]; 
            case '+': return [$a[0]+$escalar, $a[1]+$escalar, $a[2]+$escalar]; 
            case '-': return [$a[0]-$escalar, $a[1]-$escalar, $a[2]-$escalar]; 
            case '/': return [$a[0]/$escalar, $a[1]/$escalar, $a[2]/$escalar]; 
        }
    }

    public static function length($a): float{
        return sqrt(Vec3::length_squared($a));
    }
        
    public static function length_squared($a): float {
        return $a[0]*$a[0] + $a[1]*$a[1] + $a[2]*$a[2];
    }

    public static function write_color(array $a, $samples_per_pixel) {
        $r = $a[0];
        $g = $a[1];
        $b = $a[2];
        $scale = 1.0 / $samples_per_pixel;
        //$r *= $scale;
        //$g *= $scale;
        //$b *= $scale;
        $r = sqrt($scale * $r);
        $g = sqrt($scale * $g);
        $b = sqrt($scale * $b);

        $ir = intval(256 * clamp($r, 0.0, 0.999));
        $ig = intval(256 * clamp($g, 0.0, 0.999));
        $ib = intval(256 * clamp($b, 0.0, 0.999));

        return $ir . ' ' . $ig . ' ' . $ib . "\n";
    }
    
    public static function unit_vector($a): array {
        return Vec3::escalar_op('/', $a, Vec3::length($a));
    }
    
    public static function dot($a, $b): float {
        return $a[0] * $b[0]
             + $a[1] * $b[1]
             + $a[2] * $b[2];
    }

    public static function cross($a, $b): array {
        return [$a[1] * $b[2] - $a[2] * $b[1],
                    $a[2] * $b[0] - $a[0] * $b[2],
                    $a[0] * $b[1] - $a[1] * $b[0]];
    }

    public static function negative($a): array{
        return [-$a[0],-$a[1],-$a[2]];
    }

    public static function random(): array {
        return [random_double(), random_double(), random_double()];
    }

    public static function random_r(float $min, float $max) {
        return [random_double($min,$max), random_double($min,$max), random_double($min,$max)];
    }
    
    public static function near_zero($e) {
        // Return true if the vector is close to zero in all dimensions.
        $s = pow(10, -8);//1e-8;
        return (abs($e[0]) < $s) && (abs($e[1]) < $s) && ($abs($e[2]) < $s);
    }

    public static function reflect(array $v, array $n): array {
        $tmp = Vec3::escalar_op('*',$n,2*Vec3::dot($v,$n)); 
        return Vec3::op('-',$v,$tmp);
        //return v - 2*dot(v,n)*n;
    }

    public static function refract(array $uv, array $n, float $etai_over_etat) {
        $cos_theta = min(Vec3::dot(Vec3::negative($uv), $n), 1.0);
        //r_out_perp =  etai_over_etat * (uv + cos_theta*n);
        $r_out_perp = Vec3::escalar_op('*',$n,$cos_theta);
        $r_out_perp = Vec3::op('+',$r_out_perp,$uv); 
        $r_out_perp = Vec3::escalar_op('*',$r_out_perp,$etai_over_etat);
        
        //vec3 r_out_parallel = -sqrt(fabs(1.0 - r_out_perp.length_squared())) * n;
        $r_out_parallel = sqrt(abs(1 - Vec3::length_squared($r_out_perp)));
        $r_out_parallel = Vec3::escalar_op('*',$n,$r_out_parallel);
        $r_out_parallel = Vec3::negative($r_out_parallel);
        return Vec3::op('+', $r_out_perp, $r_out_parallel);
    }
}


