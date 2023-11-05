<?php declare(strict_types = 1);

namespace App\Domain\Api\Facade;

use App\Domain\Api\Request\CreateDeviceReqDto;
use App\Domain\Api\Response\DeviceResDto;
use App\Domain\Device\Device;
use App\Model\Database\EntityManagerDecorator;
use App\Model\Exception\Logic\InvalidArgumentException;
use App\Model\Exception\Runtime\Database\EntityNotFoundException;

final class DevicesFacade
{

	public function __construct(private EntityManagerDecorator $em)
	{
	}

	/**
	 * @param mixed[] $criteria
	 * @param string[] $orderBy
	 * @return DeviceResDto[]
	 */
	public function findBy(array $criteria = [], array $orderBy = ['id' => 'ASC'], int $limit = 10, int $offset = 0): array
	{
		$entities = $this->em->getRepository(Device::class)->findBy($criteria, $orderBy, $limit, $offset);
		$result = [];

		foreach ($entities as $entity) {
			$result[] = DeviceResDto::from($entity);
		}

		return $result;
	}

	/**
	 * @return DeviceResDto[]
	 */
	public function findAll(int $limit = 10, int $offset = 0): array
	{
		return $this->findBy([], ['id' => 'ASC'], $limit, $offset);
	}

	/**
	 * @param mixed[] $criteria
	 * @param string[] $orderBy
	 */
	public function findOneBy(array $criteria, ?array $orderBy = null): DeviceResDto
	{
		$entity = $this->em->getRepository(Device::class)->findOneBy($criteria, $orderBy);

		if ($entity === null) {
			throw new EntityNotFoundException();
		}

		return DeviceResDto::from($entity);
	}

	public function findOne(int $id): DeviceResDto
	{
		return $this->findOneBy(['id' => $id]);
	}

	/**
	 * @param CreateDeviceReqDto $dto
	 * @return Device
	 */
	public function create(CreateDeviceReqDto $dto): Device
	{
		$device = new Device(
			$dto->name,
			$dto->serialNumber
		);

		$device->setDeviceType($dto->deviceType);

		$device->setCreatedAt();
		$device->setUpdatedAt();

		$this->em->persist($device);
		$this->em->flush($device);

		return $device;
	}

	/**
	 * @param int $id
	 * @param CreateDeviceReqDto $dto
	 * @return Device
	 */
	public function update(int $id, CreateDeviceReqDto $dto): Device
	{
		$device = $this->em->getRepository(Device::class)->findOneBy(['id' => $id]);

		if ($device === null) {
			throw new EntityNotFoundException();
		}

		$device->setName($dto->name);
		$device->setSerialNumber($dto->serialNumber);
		$device->setDeviceType($dto->deviceType);
		$device->setUpdatedAt();

		$this->em->persist($device);
		$this->em->flush($device);

		return $device;
	}

	public function delete(int $id) {
		$device = $this->em->getRepository(Device::class)->findOneBy(['id' => $id]);

		if ($device === null) {
			throw new EntityNotFoundException();
		}

		$this->em->remove($device);
		$this->em->flush();
	}

	/**
	 * @param CreateDeviceReqDto $dto
	 * @return bool
	 * @throws InvalidArgumentException
	 */
	public function validateDto(CreateDeviceReqDto $dto) {
		if (empty($dto->name))
			throw new InvalidArgumentException('Missing attribute "name"');
		if (empty($dto->serialNumber))
			throw new InvalidArgumentException('Missing attribute "serialNumber"');
		if (empty($dto->deviceType))
			throw new InvalidArgumentException('Missing attribute "deviceType"');

		return true;

	}

}
