<?php

// app/Core/Logger.php
// Componente de Auditoría — Guía Práctica N°. 8
// Arquitectura y Diseño de Software — andmen05 v1.0

namespace App\Core;

/**
 * Logger — Clase de Auditoría y Trazabilidad
 *
 * Registra eventos del sistema en /logs/audit.log con el formato:
 *   [FECHA_HORA] [TIPO_EVENTO] [MENSAJE_DETALLADO]
 *
 * Tipos de evento soportados:
 *   INFO     → Operaciones normales del sistema
 *   WARNING  → Situaciones anómalas no críticas
 *   ERROR    → Fallos que requieren atención
 *   AUTH     → Eventos de autenticación (login / logout)
 *   DB       → Errores de base de datos
 */
class Logger
{
    /** Ruta absoluta al archivo de log */
    private string $logFile;

    /** Niveles válidos de evento */
    private const LEVELS = ['INFO', 'WARNING', 'ERROR', 'AUTH', 'DB'];

    /**
     * Constructor.
     *
     * @param string|null $logDir  Directorio donde se guarda audit.log.
     *                             Por defecto: <raíz del proyecto>/logs/
     */
    public function __construct(?string $logDir = null)
    {
        // Si no se pasa directorio, calcular desde la ubicación de este archivo
        // app/Core/Logger.php → subir 2 niveles → raíz del proyecto
        if ($logDir === null) {
            $logDir = dirname(__DIR__, 2) . DIRECTORY_SEPARATOR . 'logs';
        }

        // Crear el directorio si todavía no existe
        if (!is_dir($logDir)) {
            mkdir($logDir, 0755, true);
        }

        $this->logFile = rtrim($logDir, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . 'audit.log';
    }

    // ─────────────────────────────────────────────────────────────────────────
    // Método principal
    // ─────────────────────────────────────────────────────────────────────────

    /**
     * Escribe un registro en el archivo audit.log.
     *
     * Formato de salida:
     *   [2026-04-21 14:35:07] [AUTH] [Login fallido para usuario: admin@test.com]
     *
     * @param string $mensaje   Descripción detallada del evento.
     * @param string $tipo      Tipo de evento (INFO, WARNING, ERROR, AUTH, DB).
     *
     * @return void
     */
    public function log(string $mensaje, string $tipo = 'INFO'): void
    {
        $tipo = strtoupper($tipo);

        // Validar tipo; si no es válido, usar WARNING
        if (!in_array($tipo, self::LEVELS, true)) {
            $tipo = 'WARNING';
        }

        $fechaHora = date('Y-m-d H:i:s');
        $linea     = "[{$fechaHora}] [{$tipo}] [{$mensaje}]" . PHP_EOL;

        // Escritura atómica con bloqueo de archivo para evitar condiciones de carrera
        file_put_contents($this->logFile, $linea, FILE_APPEND | LOCK_EX);
    }

    // ─────────────────────────────────────────────────────────────────────────
    // Métodos de conveniencia
    // ─────────────────────────────────────────────────────────────────────────

    /** Registra un evento de tipo INFO */
    public function info(string $mensaje): void
    {
        $this->log($mensaje, 'INFO');
    }

    /** Registra un evento de tipo WARNING */
    public function warning(string $mensaje): void
    {
        $this->log($mensaje, 'WARNING');
    }

    /** Registra un evento de tipo ERROR */
    public function error(string $mensaje): void
    {
        $this->log($mensaje, 'ERROR');
    }

    /** Registra un evento de autenticación */
    public function auth(string $mensaje): void
    {
        $this->log($mensaje, 'AUTH');
    }

    /** Registra un evento de base de datos */
    public function db(string $mensaje): void
    {
        $this->log($mensaje, 'DB');
    }
}
