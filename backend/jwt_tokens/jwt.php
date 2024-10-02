<?php

class JWT {
    public static function encode($data, $key) {
        $header = json_encode(['typ' => 'JWT', 'alg' => 'HS256']);
        $payload = json_encode($data);

        $base64UrlHeader = str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($header));
        $base64UrlPayload = str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($payload));
        
        $signature = hash_hmac('sha256', $base64UrlHeader . "." . $base64UrlPayload, $key, true);
        $base64UrlSignature = str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($signature));
        
        return $base64UrlHeader . "." . $base64UrlPayload . "." . $base64UrlSignature;
    }

    public static function decode($jwt, $key) {
        $parts = explode('.', $jwt);
        if (count($parts) !== 3) {
            return false;
        }

        list($base64UrlHeader, $base64UrlPayload, $base64UrlSignature) = $parts;

        $header = json_decode(base64_decode(str_replace(['-', '_'], ['+', '/'], $base64UrlHeader)), true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            return false;
        }
        
        $payload = json_decode(base64_decode(str_replace(['-', '_'], ['+', '/'], $base64UrlPayload)), true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            return false;
        }
        
        $signature = base64_decode(str_replace(['-', '_'], ['+', '/'], $base64UrlSignature));
        $validSignature = hash_hmac('sha256', $base64UrlHeader . "." . $base64UrlPayload, $key, true);

        if (hash_equals($signature, $validSignature)) {
            return $payload;
        } else {
            return false;
        }
    }
}