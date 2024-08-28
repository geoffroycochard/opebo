<?php
declare(strict_types=1);

namespace App\Service\Domain;
use PDO;

final class Synonym
{
    private PDO $pdo;

    public function __construct(string $databaseUrl)
    {
        $this->pdo = new PDO($databaseUrl);
        $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    }

    public function retrieve(array $words): array
    {
        $return = [];
        $clause = implode(',', array_fill(0, count($words), '?'));
        $query = "SELECT * FROM thesaurus_fr WHERE synonymes_racine IN ($clause)";
        $stmt = $this->pdo->prepare($query);
        $stmt->execute($words);
        foreach ($stmt->fetchAll() as $value) {
            $return = array_merge($return, explode(',', $value['synonymes_mots']));
        }

        return $return;
    }
    
}
