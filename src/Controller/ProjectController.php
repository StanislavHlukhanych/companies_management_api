<?php

namespace App\Controller;

use App\Entity\Project;
use App\Repository\CompanyRepository;
use App\Repository\EmployeeRepository;
use App\Repository\ProjectRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Serializer\SerializerInterface;

final class ProjectController extends AbstractController
{
    #[Route('/api/projects', name: 'app_project', methods: ['GET'])]
    public function index(ProjectRepository $projectRepository): JsonResponse
    {
        $projects = $projectRepository->findAll();

        return $this->json($projects, Response::HTTP_OK, [], ['groups' => 'project:read']);
    }

    #[Route('/api/projects/{id}', name: 'app_project_show', methods: ['GET'])]
    public function show(ProjectRepository $projectRepository, int $id): JsonResponse
    {
        $project = $projectRepository->find($id);
        if (!$project) {
            return $this->json(['error' => 'Project not found'], Response::HTTP_NOT_FOUND);
        }

        return $this->json($project, Response::HTTP_OK, [], ['groups' => 'project:read']);
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
        $project = $serializer->deserialize(json_encode($data), Project::class, 'json', ['groups' => 'project:write']);

        $companyId = $data['companyId'] ?? null;
        if (!$companyId) {
            return $this->json(['error' => 'Company ID is required'], Response::HTTP_BAD_REQUEST);
        }

        $company = $companyRepository->find($companyId);
        if (!$company) {
            return $this->json(['error' => 'Company not found'], Response::HTTP_NOT_FOUND);
        }

        $project->setCompany($company);

        if (isset($data['participantIds']) && is_array($data['participantIds'])) {
            foreach ($data['participantIds'] as $participantId) {
                $employee = $employeeRepository->find($participantId);
                if (!$employee) {
                    return $this->json(['error' => sprintf('Employee with id %d not found', $participantId)], Response::HTTP_NOT_FOUND);
                }
                $project->addParticipant($employee);
            }
        }

        $entityManager->persist($project);
        $entityManager->flush();

        return $this->json(['message' => 'Project created successfully'], Response::HTTP_CREATED);
    }

    #[Route('/api/projects/{id}', name: 'app_project_update', methods: ['PUT'])]
    public function update(
        Request $request,
        ProjectRepository $projectRepository,
        CompanyRepository $companyRepository,
        EmployeeRepository $employeeRepository,
        EntityManagerInterface $entityManager,
        SerializerInterface $serializer,
        int $id
    ): JsonResponse
    {
        $project = $projectRepository->find($id);
        if (!$project) {
            return $this->json(['error' => 'Project not found'], Response::HTTP_NOT_FOUND);
        }

        $data = $request->toArray();
        $serializer->deserialize(json_encode($data), Project::class, 'json',
            ['object_to_populate' => $project, 'groups' => 'project:write']);

        if (isset($data['companyId'])) {
            $company = $companyRepository->find($data['companyId']);
            if (!$company) {
                return $this->json(['error' => 'Company not found'], Response::HTTP_NOT_FOUND);
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
                    return $this->json(['error' => sprintf('Employee with id %d not found', $participantId)], Response::HTTP_NOT_FOUND);
                }
                $project->addParticipant($employee);
            }
        }

        $entityManager->flush();

        return $this->json(['message' => 'Project updated successfully'], Response::HTTP_OK);
    }

    #[Route('/api/projects/{id}', name: 'app_project_delete', methods: ['DELETE'])]
    public function delete(ProjectRepository $projectRepository, EntityManagerInterface $entityManager, int $id): JsonResponse
    {
        $project = $projectRepository->find($id);
        if (!$project) {
            return $this->json(['error' => 'Project not found'], Response::HTTP_NOT_FOUND);
        }

        $entityManager->remove($project);
        $entityManager->flush();

        return $this->json(['message' => 'Project deleted successfully'], Response::HTTP_OK);
    }
}
