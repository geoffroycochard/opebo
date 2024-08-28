<?php
declare(strict_types=1);

namespace App\Service\Domain;

final class Calculator 
{
    private array $initial = [
        'source' => [],
        'compare' => []
    ];

    private array $source;

    private array $compare;

    private const EXCLUDE = [
        '', 'et', 'pour',
        'l\'', 'le', 'la', 'les', 'en',
        'un', 'une', 'des', 'd\'', 'de',
        'ce', 'cet', 'cette', 'ces',
        'mon', 'ton', 'son', 'ma', 'ta', 'sa', 'mes', 'tes', 'ses', 'notre', 'votre', 'leur', 'nos', 'vos', 'leurs',
        'quel', 'quelle', 'quels', 'quelles',
        'des', 'du', 'de la', 'de l\'', 'd\'',
        'of',
        ':', '/', '-'
    ];

    public function __construct(
        private Synonym $synonym
    ){}

    public function setSource(array $words): void
    {
        $this->fill($words, 'source');
    }

    public function setCompare(array $words): void
    {
        $this->fill($words, 'compare');
    }

    private function fill(array $words, string $item): void
    {
        $forSynomyms = $this->extract($words);
        $forSynomyms = array_diff($forSynomyms, self::EXCLUDE);
        $this->initial[$item] = $words;
        sort($forSynomyms);
        $elements = array_unique(
            array_merge(
                $words,
                $this->synonym->retrieve($forSynomyms)
            )
        );
        $this->$item = $elements;
    }

    private function extract(array $words): array
    {
        $source = [];
        foreach ($words as $word) {
            $source = array_merge($source, explode(' ', $word));
        }
        return array_unique($source);

    }

    public function scoring(int $boost): float
    {
        // Synonym scoring
        $synonym = 0;
        $intersect = array_intersect($this->source, $this->compare);
        // On 100%
        if($intersect) {
            $synonym = (count($intersect)*100)/count($this->compare);
        }
        
        // Racine scoring from origin words
        $point = 0;
        foreach ($this->initial['source'] as  $source) {
            foreach ($this->initial['compare'] as  $compare) {
                similar_text($source, $compare, $percent);
                $point += ($percent > 70) ? 5 : (($percent > 60) ? 3 : (($percent > 40) ? 2 : 0));
            }    
        }

        return round(((($synonym) * (1 + 0.3) + $point * (1 + 0.7)) * $boost)/10);
    }
}


