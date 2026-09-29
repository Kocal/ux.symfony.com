<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Controller\UxPackage;

use App\Service\UxPackageRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Profiler\Profiler;
use Symfony\Component\Routing\Attribute\Route;

class InspectorController extends AbstractController
{
    #[Route('/inspector', name: 'app_inspector')]
    public function __invoke(UxPackageRepository $packageRepository): Response
    {
        return $this->render('ux_packages/inspector.html.twig', [
            'package' => $packageRepository->find('inspector'),
            'covered' => array_map(
                $packageRepository->find(...),
                ['stimulus', 'live-component', 'turbo'],
            ),
        ]);
    }

    #[Route('/demos/inspector/results', name: 'app_demo_inspector_results')]
    public function results(Request $request): Response
    {
        return $this->render('ux_packages/inspector/_results.html.twig', [
            'query' => $request->query->getString('q'),
        ], new Response(headers: ['X-Robots-Tag' => 'noindex']));
    }

    #[Route('/demos/inspector/remove/{product}', name: 'app_demo_inspector_remove', requirements: ['product' => '[1-6]'], methods: ['GET'])]
    public function remove(int $product): Response
    {
        return $this->render('ux_packages/inspector/_remove.stream.html.twig', [
            'product' => $product,
        ], new Response(headers: ['Content-Type' => 'text/vnd.turbo-stream.html', 'X-Robots-Tag' => 'noindex']));
    }

    #[Route('/demos/inspector/{page}', name: 'app_demo_inspector', requirements: ['page' => 'find|inspect|connect|trace'], defaults: ['page' => 'find'])]
    public function demo(
        string $page,
        #[Autowire(service: 'profiler')] ?Profiler $profiler = null,
    ): Response {
        // Keeps the web debug toolbar out of the demo iframe.
        $profiler?->disable();

        return $this->render('ux_packages/inspector/_demo_page.html.twig', [
            'page' => $page,
        ]);
    }
}
