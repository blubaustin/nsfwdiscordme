<?php
namespace App\Twig;

use App\Repository\CategoryRepository;
use Twig\Extension\AbstractExtension;
use Twig\Extension\GlobalsInterface;
use Twig\TwigFilter;

/**
 * Class GlobalsExtension
 */
class GlobalsExtension extends AbstractExtension implements GlobalsInterface
{
    /**
     * @var
     */
    protected $categoryRepository;

    /**
     * Constructor
     *
     * @param CategoryRepository $categoryRepository
     */
    public function __construct(CategoryRepository $categoryRepository)
    {
        $this->categoryRepository = $categoryRepository;
    }

    /**
     * {@inheritDoc}
     */
    public function getGlobals()
    {
        return [
            'categories' => $this->categoryRepository->findAll()
        ];
    }

    /**
     * @return TwigFilter[]
     */
    public function getFilters()
    {
        return [
            new TwigFilter('toBool', [$this, 'toBool'])
        ];
    }

    /**
     * @param mixed $value
     *
     * @return string
     */
    public function toBool($value)
    {
        return $value ? 'true' : 'false';
    }
}
