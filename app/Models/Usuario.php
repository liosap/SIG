<?php
declare(strict_types=1);

namespace App\Models;

class Usuario
{
    public ?int $ID_Usuario = null;
    public ?string $Username = null;
    public ?string $PasswordHash = null;

    /**
     * Constructor opcional para inicializar datos
     *
     * @param array|null $data
     */
    public function __construct(?array $data = null)
    {
        if ($data) {
            $this->ID_Usuario   = $data['ID_Usuario'] ?? null;
            $this->Username     = $data['Username'] ?? null;
            $this->PasswordHash = $data['PasswordHash'] ?? null;
        }
    }
}
