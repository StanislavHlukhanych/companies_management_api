<?php

namespace App\Controller;

use App\Entity\Project;
use App\Repository\CompanyRepository;
use App\Repository\EmployeeRepository;
use App\Repository\ProjectRepository;
use App\Trait\ApiResponseTrait;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Serializer\SerializerInterface;

final class ProjectController extends AbstractController
{
    use ApiResponseTrait;

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
    public function create(
        Request $request,
        EntityManagerInterface $entityManager,
        SerializerInterface $serializer,
        CompanyRepository $companyRepository,
        EmployeeRepository $employeeRepository
    ): JsonResponse
    {
        $data = $request->toArray();

        $companyId = $data['companyId'] ?? null;
        if (!$companyId) {
            return $this->fail([
                'companyId' => 'This field is required'
            ], Response::HTTP_BAD_REQUEST);
        }

        $company = $companyRepository->find($companyId);
        if (!$company) {
            return $this->fail([
                'companyId' => 'Company with the provided ID does not exist'
            ], Response::HTTP_NOT_FOUND);
        }

        $project = $serializer->deserialize(
            $request->getContent(),
            Project::class,
            'json',
            ['groups' => 'project:write']);

        $project->setCompany($company);

        if (isset($data['participantIds']) && is_array($data['participantIds'])) {
            foreach ($data['participantIds'] as $participantId) {
                $employee = $employeeRepository->find($participantId);
                if (!$employee) {
                    return $this->fail([
                        'participantIds' => "Employee with ID $participantId not found"
                    ], Response::HTTP_NOT_FOUND);
                }
                $project->addParticipant($employee);
            }
        }

        $entityManager->persist($project);
        $entityManager->flush();

        return $this->success($project, Response::HTTP_CREATED, ['project:read']);
    }

    #[Route('/api/projects/{id}', name: 'app_project_update', methods: ['PUT'])]
    public function update(
        Request $request,
        CompanyRepository $companyRepository,
        EmployeeRepository $employeeRepository,
        EntityManagerInterface $entityManager,
        SerializerInterface $serializer,
        Project $project
    ): JsonResponse
    {
        $data = $request->toArray();
        $serializer->deserialize(
            $request->getContent(),
            Project::class,
            'json',
            ['object_to_populate' => $project, 'groups' => 'project:write']);

        if (isset($data['companyId'])) {
            $company = $companyRepository->find($data['companyId']);
            if (!$company) {
                return $this->fail([
                    'companyId' => 'Company with the provided ID does not exist'
                ], Response::HTTP_NOT_FOUND);
            }
            $project->setCompany($company);
        }

        if (isset($data['participantIds']) && is_array($data['participantIds'])) {
            foreach ($project->getParticipants()->toArray() as $participant) {
                $project->removeParticipant($participant);
            }

            foreach ($data['participantIds'] as $participantId) {
                $employee = $employeeRepository->find($participantId);
                if (!$employee) {
                    return $this->fail([
                        'participantIds' => "Employee with ID $participantId not found"
                    ], Response::HTTP_NOT_FOUND);
                }
                $project->addParticipant($employee);
            }
        }

        $entityManager->flush();

        return $this->success($project, Response::HTTP_OK, ['project:read']);
    }

    #[Route('/api/projects/{id}', name: 'app_project_delete', methods: ['DELETE'])]
    public function delete(
        EntityManagerInterface $entityManager,
        Project $project
    ): JsonResponse
    {
        $entityManager->remove($project);
        $entityManager->flush();

        return $this->success(null, Response::HTTP_OK);
    }
}
