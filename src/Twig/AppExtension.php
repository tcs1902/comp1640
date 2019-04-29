<?php

namespace App\Twig;

use App\Entity\User;
use App\Utils\UploaderHelper;
use Psr\Container\ContainerInterface;
use Symfony\Contracts\Service\ServiceSubscriberInterface;
use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;
use Twig\TwigFunction;

class AppExtension extends AbstractExtension implements ServiceSubscriberInterface
{
    /**
     * @var ContainerInterface
     */
    private $container;

    /**
     * AppExtension constructor.
     *
     * @param ContainerInterface $container
     */
    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    public function getFilters(): array
    {
        return [
            // If your filter generates SAFE HTML, you should add a third
            // parameter: ['is_safe' => ['html']]
            // Reference: https://twig.symfony.com/doc/2.x/advanced.html#automatic-escaping
            new TwigFilter('filter_name', [$this, 'doSomething']),
            new TwigFilter('my_faculty', [$this, 'filterFacultyByUser']),
        ];
    }

    public static function getSubscribedServices()
    {
        return [
            UploaderHelper::class,
        ];
    }

    public function getFunctions(): array
    {
        return [
            new TwigFunction('uploaded_asset', [$this, 'getUploadedAssetPath']),
        ];
    }

    public function getUploadedAssetPath(string $path): string
    {
        return $this->container
            ->get(UploaderHelper::class)
            ->getPublicPath($path);
    }

    public function filterFacultyByUser(array $facultyArray, User $user): array
    {
        if (!$user->isBelongToAFaculty()) {
            return $facultyArray;
        }

        return array_filter($facultyArray, function ($facultyItem) use ($user) {
            return $user->getFaculty() === $facultyItem['faculty'];
        });
    }
}
