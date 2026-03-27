<?php

namespace App\Controller;

use App\Dto\ProjectDto;
use App\Entity\Project;
use App\Repository\ProjectRepository;
use App\Service\ProjectService;
use App\Trait\ApiResponseTrait;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Routing\Attribute\Route;

final class ProjectController extends AbstractController
{
    use ApiResponseTrait;

    public function __construct(
        private readonly ProjectService $projectService,
    ){}

    #[Route('/api/projects', name: 'app_project', methods: ['GET'])]
    public function index(ProjectRepository $projectRepository): JsonResponse
    {
        $projects = $projectRepository->findAll();

        return $this->success($projects, Response::HTTP_OK, ['project:read']);
    }

    #[Route('/api/projects/{id}', name: 'app_project_show', methods: ['GET'])]
    public function show(Project $project): JsonResponse
    {
        return $this->success($project, Response::HTTP_OK, ['project:read']);
    }

    #[Route('/api/projects', name: 'app_project_create', methods: ['POST'])]
    public function create(#[MapRequestPayload] ProjectDto $projectDto): JsonResponse
    {
        $project = $this->projectService->create($projectDto);

        return $this->success($project, Response::HTTP_CREATED, ['project:read']);
    }

    #[Route('/api/projects/{id}', name: 'app_project_update', methods: ['PUT'])]
    public function update(Project $project, #[MapRequestPayload] ProjectDto $projectDto): JsonResponse
    {
        $project = $this->projectService->update($project, $projectDto);

        return $this->success($project, Response::HTTP_OK, ['project:read']);
    }

    #[Route('/api/projects/{id}', name: 'app_project_delete', methods: ['DELETE'])]
    public function delete(Project $project): JsonResponse
    {
        $this->projectService->delete($project);

        return $this->success(null, Response::HTTP_OK);
    }
}
