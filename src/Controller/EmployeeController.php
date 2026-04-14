<?php

namespace App\Controller;

use App\Dto\EmployeeDto;
use App\Entity\Employee;
use App\Repository\EmployeeRepository;
use App\Service\EmployeeService;
use App\Trait\ApiResponseTrait;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Routing\Attribute\Route;

final class EmployeeController extends AbstractController
{
    use ApiResponseTrait;

    public function __construct(
        private readonly EmployeeService $employeeService,
    ){}

    #[Route('/api/employees', name: 'app_employee', methods: ['GET'])]
    public function index(EmployeeRepository $employeeRepository): JsonResponse
    {
        $employees = $employeeRepository->findAll();

        return $this->success($employees, Response::HTTP_OK, ['employee:read']);
    }

    #[Route('/api/employees/{id}', name: 'app_employee_show', methods: ['GET'])]
    public function show(Employee $employee): JsonResponse
    {
        return $this->success($employee, Response::HTTP_OK, ['employee:read']);
    }

    #[Route('/api/employees', name: 'app_employee_create', methods: ['POST'])]
    public function create(#[MapRequestPayload] EmployeeDto $employeeDto): JsonResponse
    {
        $employee = $this->employeeService->create($employeeDto);

        return $this->success($employee, Response::HTTP_CREATED, ['employee:read']);
    }

    #[Route('/api/employees/{id}', name: 'app_employee_update', methods: ['PUT'])]
    public function update(Employee $employee, #[MapRequestPayload] EmployeeDto $employeeDto): JsonResponse
    {
        $employee = $this->employeeService->update($employee, $employeeDto);

        return $this->success($employee, Response::HTTP_OK, ['employee:read']);
    }

    #[Route('/api/employees/{id}', name: 'app_employee_delete', methods: ['DELETE'])]
    public function delete(Employee $employee): JsonResponse
    {
        $this->employeeService->delete($employee);

        return $this->success(null, Response::HTTP_OK);
    }
}
