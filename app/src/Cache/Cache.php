<?php

namespace App\Cache;

/**
 * Clase para gestionar el sistema de caché
 * 
 * Implementa un sistema de caché en memoria utilizando el patrón Singleton
 */
class Cache {
    /** @var Cache|null Instancia única de la clase Cache */
    private static $instance = null;

    /** @var array Almacén de datos en caché */
    private $storage = [];

    /** @var array Tiempo de expiración de cada clave */
    private $expiration = [];

    /**
     * Constructor privado para prevenir la creación directa de objetos
     */
    private function __construct() {}

    /**
     * Obtiene la instancia única de la clase Cache
     * 
     * @return Cache Instancia única de Cache
     */
    public static function getInstance(): self {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Guarda un valor en la caché
     * 
     * @param string $key Clave para identificar el valor
     * @param mixed $value Valor a almacenar
     * @param int $ttl Tiempo de vida en segundos (0 = sin expiración)
     * @return void
     */
    public function set(string $key, $value, int $ttl = 300): void {
        $this->storage[$key] = $value;
        if ($ttl > 0) {
            $this->expiration[$key] = time() + $ttl;
        }
    }

    /**
     * Obtiene un valor de la caché
     * 
     * @param string $key Clave del valor a obtener
     * @return mixed|null El valor almacenado o null si no existe o expiró
     */
    public function get(string $key) {
        if (!$this->has($key)) {
            return null;
        }
        return $this->storage[$key];
    }

    /**
     * Verifica si una clave existe y no ha expirado
     * 
     * @param string $key Clave a verificar
     * @return bool True si la clave existe y no ha expirado
     */
    public function has(string $key): bool {
        if (!isset($this->storage[$key])) {
            return false;
        }

        if (isset($this->expiration[$key]) && time() > $this->expiration[$key]) {
            $this->delete($key);
            return false;
        }

        return true;
    }

    /**
     * Elimina una clave de la caché
     * 
     * @param string $key Clave a eliminar
     * @return void
     */
    public function delete(string $key): void {
        unset($this->storage[$key]);
        unset($this->expiration[$key]);
    }

    /**
     * Limpia toda la caché
     * 
     * @return void
     */
    public function clear(): void {
        $this->storage = [];
        $this->expiration = [];
    }

    /**
     * Previene la clonación del objeto
     * 
     * @return void
     * @throws \Exception
     */
    private function __clone() {
        throw new \Exception("No se permite clonar esta instancia");
    }

    /**
     * Previene la deserialización del objeto
     * 
     * @return void
     * @throws \Exception
     */
    private function __wakeup() {
        throw new \Exception("No se permite deserializar esta instancia");
    }
} 