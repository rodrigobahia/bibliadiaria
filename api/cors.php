<?php

require_once __DIR__ . '/bootstrap.php';

// Este backend atua apenas como intermediário de consulta pública; nenhuma origem não autorizada pode usar os dados.
validate_origin($config);
