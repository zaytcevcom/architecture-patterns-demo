<?php

declare(strict_types=1);

namespace App\Modules\Identity\Entity\User;

use App\Modules\Data\Entity\City\City;
use App\Modules\Data\Entity\Country\Country;
use App\Modules\Identity\Entity\Temp\UserTemp;
use App\Modules\Identity\Entity\User\Fields\Birthday;
use App\Modules\Identity\Entity\User\Fields\Blocked;
use App\Modules\Identity\Entity\User\Fields\Deactivated;
use App\Modules\Identity\Entity\User\Fields\Deleted;
use App\Modules\Identity\Entity\User\Fields\Email;
use App\Modules\Identity\Entity\User\Fields\FirstName;
use App\Modules\Identity\Entity\User\Fields\LastName;
use App\Modules\Identity\Entity\User\Fields\Marital;
use App\Modules\Identity\Entity\User\Fields\Phone;
use App\Modules\Identity\Entity\User\Fields\Photo;
use App\Modules\Identity\Entity\User\Fields\PhotoFileId;
use App\Modules\Identity\Entity\User\Fields\PhotoHost;
use App\Modules\Identity\Entity\User\Fields\ScreenName;
use App\Modules\Identity\Entity\User\Fields\Sex;
use App\Modules\Identity\Entity\User\Fields\Site;
use App\Modules\Identity\Entity\User\Fields\Verified;
use Doctrine\ORM\Mapping as ORM;
use DomainException;

#[ORM\Entity]
#[ORM\Table(name: 'users')]
#[ORM\Index(fields: ['screenName'], name: 'IDX_SEARCH_SCREEN_NAME')]
#[ORM\Index(fields: ['firstName'], name: 'IDX_SEARCH_FIRST_NAME')]
#[ORM\Index(fields: ['lastName'], name: 'IDX_SEARCH_LAST_NAME')]
class User
{
    private const DELAY_ONLINE = 5 * 60;

    #[ORM\Id]
    #[ORM\Column(type: 'integer', unique: true, options: ['unsigned' => true])]
    #[ORM\GeneratedValue(strategy: 'AUTO')]
    private ?int $id = null;

    #[ORM\Column(type: 'integer', nullable: true)]
    private ?int $mainMethod;

    #[ORM\Column(type: 'user_screenname', length: 100, nullable: true)]
    private ?ScreenName $screenName = null;

    #[ORM\Column(type: 'user_firstname', length: 50)]
    private FirstName $firstName;

    #[ORM\Column(type: 'user_lastname', length: 50)]
    private LastName $lastName;

    #[ORM\Column(type: 'user_firstname', length: 50, nullable: true)]
    private ?FirstName $firstNameTranslit = null;

    #[ORM\Column(type: 'user_lastname', length: 50, nullable: true)]
    private ?LastName $lastNameTranslit = null;

    #[ORM\Column(type: 'user_verified')]
    private Verified $verified;

    #[ORM\Column(type: 'user_deactivated')]
    private Deactivated $deactivated;

    #[ORM\Column(type: 'user_deleted', nullable: true)]
    private Deleted $deleted;

    #[ORM\Column(type: 'user_blocked', nullable: true)]
    private Blocked $blocked;

    #[ORM\Column(type: 'user_phone', length: 15, unique: true, nullable: true)]
    private ?Phone $phone = null;

    #[ORM\Column(type: 'user_email', length: 255, unique: true, nullable: true)]
    private ?Email $email = null;

    #[ORM\Column(type: 'string', length: 255)]
    private string $password;

    #[ORM\Column(type: 'integer', nullable: true)]
    private ?int $passwordDate = null;

    #[ORM\ManyToOne(targetEntity: Country::class)]
    #[ORM\JoinColumn(name: 'country_id', referencedColumnName: 'id', unique: false)]
    private ?Country $country = null;

    #[ORM\Column(type: 'string', nullable: true)]
    private ?string $countryName = null;

    #[ORM\ManyToOne(targetEntity: City::class)]
    #[ORM\JoinColumn(name: 'city_id', referencedColumnName: 'id', unique: false)]
    private ?City $city = null;

    #[ORM\Column(type: 'string', nullable: true)]
    private ?string $cityName = null;

    #[ORM\Column(type: 'integer')]
    private int $rate;

    #[ORM\Column(type: 'integer')]
    private int $rateInfo;

    /** @psalm-suppress PropertyNotSetInConstructor */
    #[ORM\Column(type: 'integer')]
    private int $lastVisit;

    #[ORM\Column(type: 'string', nullable: true)]
    private ?string $status = null;

    #[ORM\Column(type: 'integer', nullable: true)]
    private ?int $photoId = null;

    #[ORM\Column(type: 'integer', nullable: true)]
    private ?int $albumProfileId = null;

    #[ORM\Column(type: 'user_photo', nullable: true)]
    private ?Photo $photo = null;

    #[ORM\Column(type: 'string', nullable: true)]
    private ?string $photoFile = null;

    #[ORM\Column(type: 'user_photo_host', nullable: true)]
    private ?PhotoHost $photoHost = null;

    #[ORM\Column(type: 'user_photo_file_id', nullable: true)]
    private ?PhotoFileId $photoFileId = null;

    #[ORM\Column(type: 'user_sex', nullable: true)]
    private ?Sex $sex;

    #[ORM\Column(type: 'user_marital', nullable: true)]
    private ?Marital $marital = null;

    #[ORM\Column(type: 'integer', nullable: true)]
    private ?int $maritalId = null;

    #[ORM\Column(type: 'user_birthday', nullable: true)]
    private ?Birthday $birthday = null;

    #[ORM\Column(type: 'integer', nullable: true)]
    private ?int $birthdayPrivacy = null;

    #[ORM\ManyToOne(targetEntity: Country::class)]
    #[ORM\JoinColumn(name: 'contacts_country_id', referencedColumnName: 'id', unique: false)]
    private ?Country $contactCountry = null;

    #[ORM\Column(name: 'contacts_country_name', type: 'string', nullable: true)]
    private ?string $contactCountryName = null;

    #[ORM\ManyToOne(targetEntity: City::class)]
    #[ORM\JoinColumn(name: 'contacts_city_id', referencedColumnName: 'id', unique: false)]
    private ?City $contactCity = null;

    #[ORM\Column(name: 'contacts_city_name', type: 'string', nullable: true)]
    private ?string $contactCityName = null;

    #[ORM\Column(name: 'contacts_phone', type: 'user_phone', length: 15, nullable: true)]
    private ?Phone $contactPhone = null;

    #[ORM\Column(type: 'user_email', length: 255, nullable: true)]
    private ?Email $contactsEmail = null;

    #[ORM\Column(name: 'contacts_site', type: 'user_site', length: 255, nullable: true)]
    private ?Site $contactSite = null;

    #[ORM\Column(type: 'integer', nullable: true)]
    private ?int $spaceId = null;

    #[ORM\Column(type: 'decimal', precision: 10, scale: 7, nullable: true)]
    private ?float $latitude = null;

    #[ORM\Column(type: 'decimal', precision: 10, scale: 7, nullable: true)]
    private ?float $longitude = null;

    #[ORM\Column(type: 'string', nullable: true)]
    private ?string $interestsActivities = null;

    #[ORM\Column(type: 'string', nullable: true)]
    private ?string $interestsInterests = null;

    #[ORM\Column(type: 'string', nullable: true)]
    private ?string $interestsMusic = null;

    #[ORM\Column(type: 'string', nullable: true)]
    private ?string $interestsFilms = null;

    #[ORM\Column(type: 'string', nullable: true)]
    private ?string $interestsTv = null;

    #[ORM\Column(type: 'string', nullable: true)]
    private ?string $interestsBooks = null;

    #[ORM\Column(type: 'string', nullable: true)]
    private ?string $interestsCitations = null;

    #[ORM\Column(type: 'string', nullable: true)]
    private ?string $interestsAbout = null;

    #[ORM\Column(type: 'string', nullable: true)]
    private ?string $positionPolitical = null;

    #[ORM\Column(type: 'string', nullable: true)]
    private ?string $positionReligion = null;

    #[ORM\Column(type: 'string', nullable: true)]
    private ?string $positionLife = null;

    #[ORM\Column(type: 'string', nullable: true)]
    private ?string $positionPeople = null;

    #[ORM\Column(type: 'string', nullable: true)]
    private ?string $positionSmoking = null;

    #[ORM\Column(type: 'string', nullable: true)]
    private ?string $positionAlcohol = null;

    #[ORM\Column(type: 'string', nullable: true)]
    private ?string $positionInspiredBy = null;

    #[ORM\Column(type: 'integer', options: ['default' => 0])]
    private int $countContacts = 0;

    #[ORM\Column(type: 'integer', options: ['default' => 0])]
    private int $countSubscribers = 0;

    #[ORM\Column(type: 'integer', options: ['default' => 0])]
    private int $countAudios = 0;

    #[ORM\Column(type: 'integer', options: ['default' => 0])]
    private int $countPhotos = 0;

    #[ORM\Column(type: 'integer', options: ['default' => 0])]
    private int $countAlbums = 0;

    #[ORM\Column(type: 'integer', options: ['default' => 0])]
    private int $countPosts = 0;

    #[ORM\Column(type: 'integer', options: ['default' => 0])]
    private int $countFlows = 0;

    #[ORM\Column(type: 'integer', options: ['default' => 0])]
    private int $countFlowViews = 0;

    #[ORM\Column(type: 'integer', options: ['default' => 0])]
    private int $countVideos = 0;

    #[ORM\Column(type: 'integer', options: ['default' => 0])]
    private int $countCommunities = 0;

    #[ORM\Column(type: 'integer', options: ['default' => 0])]
    private int $countPlaces = 0;

    #[ORM\Column(type: 'integer', options: ['default' => 0])]
    private int $countEvents = 0;

    #[ORM\Column(type: 'integer', options: ['default' => 0])]
    private int $countGifts = 0;

    #[ORM\Column(type: 'integer', options: ['default' => 0])]
    private int $countStocks = 0;

    #[ORM\Column(type: 'integer', nullable: true)]
    private ?int $timeReg = null;

    #[ORM\Column(type: 'integer', nullable: true)]
    private ?string $hideSections = null;

    #[ORM\Column(type: 'boolean', options: ['default' => false])]
    private bool $isBot = false;

    #[ORM\Column(type: 'string', nullable: true)]
    private ?string $maritalStatus = null;

    #[ORM\Column(type: 'string', nullable: true)]
    private ?string $relatives = null;

    #[ORM\Column(type: 'string', nullable: true)]
    private ?string $privacy = null;

    #[ORM\Column(type: 'string', nullable: true)]
    private ?string $userSearchPref = null;

    #[ORM\Column(type: 'string', nullable: true)]
    private ?string $userCountryCityName = null;

    #[ORM\Column(type: 'integer', nullable: true)]
    private ?int $year = null;

    #[ORM\Column(type: 'integer', nullable: true)]
    private ?int $month = null;

    #[ORM\Column(type: 'integer', nullable: true)]
    private ?int $day = null;

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $oldPhoto = null;

    private function __construct(
        int $mainMethod,
        FirstName $firstName,
        LastName $lastName,
        ?Sex $sex,
        string $password
    ) {
        $this->mainMethod = $mainMethod;
        $this->firstName = $firstName;
        $this->lastName = $lastName;
        $this->sex = $sex;
        $this->password = $password;

        $this->screenName = null;
        $this->verified = Verified::notVerified();
        $this->deactivated = Deactivated::notDeactivated();
        $this->blocked = Blocked::notBlocked();
        $this->deleted = Deleted::notDeleted();
        $this->marital = Marital::notSet();

        $this->passwordDate = time();
        $this->rate = 0;
        $this->rateInfo = 0;

        $this->setOnline();

        $this->timeReg = time();
        $this->isBot = false;
    }

    public static function signupByTemp(
        UserTemp $userTemp
    ): self {
        $user = new self(
            mainMethod: $userTemp->getSignupMethod(),
            firstName: $userTemp->getFirstName(),
            lastName: $userTemp->getLastName(),
            sex: $userTemp->getSex(),
            password: $userTemp->getPassword()
        );

        $user->email = $userTemp->getEmail();
        $user->phone = $userTemp->getPhone();
        $user->photoHost = $userTemp->getPhotoHost();
        $user->photoFileId = $userTemp->getPhotoFileId();

        return $user;
    }

    public static function getDelayOnline(): int
    {
        return self::DELAY_ONLINE;
    }

    public static function getAppUrl(int $id): string
    {
        return 'lo://users/' . $id;
    }

    public static function getWebUrl(int $id): string
    {
        return 'https://lo.ink/id' . $id;
    }

    /** @return array<string, string|null>|null */
    public static function getPhotoParsed(?string $photo): ?array
    {
        if (empty($photo)) {
            return null;
        }

        /** @var bool|string[]|null $sizes */
        $sizes = json_decode($photo, true);

        if (!\is_array($sizes)) {
            return null;
        }

        /** @var bool|string[]|null $cropSizes */
        $cropSizes = $sizes['crop_square'] ?? [];

        if (!\is_array($cropSizes)) {
            return null;
        }

        ksort($cropSizes);
        $cropSizes = array_values($cropSizes);

        if (\count($cropSizes) > 0) {
            return [
                'xs'        => $cropSizes[0] ?? $cropSizes[1] ?? $cropSizes[2] ?? $cropSizes[3] ?? $sizes['original'] ?? null,
                'sm'        => $cropSizes[1] ?? $cropSizes[2] ?? $cropSizes[3] ?? $sizes['original'] ?? null,
                'md'        => $cropSizes[2] ?? $cropSizes[3] ?? $sizes['original'] ?? null,
                'lg'        => $cropSizes[3] ?? $cropSizes[2] ?? $sizes['original'] ?? null,
                'original'  => $sizes['original'] ?? null,
            ];
        }

        return null;
    }

    public function getId(): int
    {
        if (null === $this->id) {
            throw new DomainException('Id not set');
        }
        return $this->id;
    }

    public function setId(?int $id): void
    {
        $this->id = $id;
    }

    public function getScreenName(): ?ScreenName
    {
        return $this->screenName;
    }

    public function setScreenName(?ScreenName $screenName): void
    {
        $this->screenName = $screenName;
    }

    public function getFirstName(): FirstName
    {
        return $this->firstName;
    }

    public function setFirstName(FirstName $firstName): void
    {
        $this->firstName = $firstName;
    }

    public function getLastName(): LastName
    {
        return $this->lastName;
    }

    public function setLastName(LastName $lastName): void
    {
        $this->lastName = $lastName;
    }

    public function getFirstNameTranslit(): ?FirstName
    {
        return $this->firstNameTranslit;
    }

    public function setFirstNameTranslit(?FirstName $firstNameTranslit): void
    {
        $this->firstNameTranslit = $firstNameTranslit;
    }

    public function getLastNameTranslit(): ?LastName
    {
        return $this->lastNameTranslit;
    }

    public function setLastNameTranslit(?LastName $lastNameTranslit): void
    {
        $this->lastNameTranslit = $lastNameTranslit;
    }

    public function getVerified(): Verified
    {
        return $this->verified;
    }

    public function setVerified(Verified $verified): void
    {
        $this->verified = $verified;
    }

    public function getDeactivated(): Deactivated
    {
        return $this->deactivated;
    }

    public function setDeactivated(Deactivated $deactivated): void
    {
        $this->deactivated = $deactivated;
    }

    public function getDeleted(): Deleted
    {
        return $this->deleted;
    }

    public function setDeleted(): void
    {
        $this->deleted = new Deleted(time());
    }

    public function getBlocked(): Blocked
    {
        return $this->blocked;
    }

    public function setBlocked(Blocked $blocked): void
    {
        $this->blocked = $blocked;
    }

    public function getPhone(): ?Phone
    {
        return $this->phone;
    }

    public function setPhone(?Phone $phone): void
    {
        $this->phone = $phone;
    }

    public function getEmail(): ?Email
    {
        return $this->email;
    }

    public function setEmail(?Email $email): void
    {
        $this->email = $email;
    }

    public function getPassword(): string
    {
        return $this->password;
    }

    public function setPassword(string $password): void
    {
        $this->password = $password;
    }

    public function getPasswordDate(): ?int
    {
        return $this->passwordDate;
    }

    public function setPasswordDate(?int $passwordDate): void
    {
        $this->passwordDate = $passwordDate;
    }

    public function getCountry(): ?Country
    {
        return $this->country;
    }

    public function setCountry(?Country $country): void
    {
        $this->country = $country;
    }

    public function getCountryName(): ?string
    {
        return $this->countryName;
    }

    public function setCountryName(?string $countryName): void
    {
        $this->countryName = $countryName;
    }

    public function getCity(): ?City
    {
        return $this->city;
    }

    public function setCity(?City $city): void
    {
        $this->city = $city;
    }

    public function getCityName(): ?string
    {
        return $this->cityName;
    }

    public function setCityName(?string $cityName): void
    {
        $this->cityName = $cityName;
    }

    public function getRate(): int
    {
        return $this->rate;
    }

    public function setRate(int $rate): void
    {
        $this->rate = $rate;
    }

    public function getRateInfo(): int
    {
        return $this->rateInfo;
    }

    public function setRateInfo(int $rateInfo): void
    {
        $this->rateInfo = $rateInfo;
    }

    public function getLastVisit(): int
    {
        return $this->lastVisit;
    }

    public function setLastVisit(int $lastVisit): void
    {
        $this->lastVisit = $lastVisit;
    }

    public function setOnline(): void
    {
        $this->setLastVisit(time());
    }

    public function getStatus(): ?string
    {
        return $this->status;
    }

    public function setStatus(?string $status): void
    {
        $this->status = $status;
    }

    public function getPhotoId(): ?int
    {
        return $this->photoId;
    }

    public function setPhotoId(int $photoId): void
    {
        $this->photoId = $photoId;
    }

    public function removePhotoId(): void
    {
        $this->photoId = null;
    }

    public function getAlbumProfileId(): ?int
    {
        return $this->albumProfileId;
    }

    public function setAlbumProfileId(?int $albumProfileId): void
    {
        $this->albumProfileId = $albumProfileId;
    }

    public function getPhoto(): ?Photo
    {
        return $this->photo;
    }

    public function setPhoto(?Photo $photo): void
    {
        $this->photo = $photo;
    }

    public function getPhotoFile(): ?string
    {
        return $this->photoFile;
    }

    public function setPhotoFile(?string $photoFile): void
    {
        $this->photoFile = $photoFile;
    }

    public function getPhotoHost(): ?PhotoHost
    {
        return $this->photoHost;
    }

    public function setPhotoHost(?PhotoHost $photoHost): void
    {
        $this->photoHost = $photoHost;
    }

    public function getPhotoFileId(): ?PhotoFileId
    {
        return $this->photoFileId;
    }

    public function setPhotoFileId(?PhotoFileId $photoFileId): void
    {
        $this->photoFileId = $photoFileId;
    }

    public function getSex(): ?Sex
    {
        return $this->sex;
    }

    public function setSex(?Sex $sex): void
    {
        $this->sex = $sex;
    }

    public function isFemale(): bool
    {
        return $this->sex === Sex::female();
    }

    public function getMarital(): ?Marital
    {
        return $this->marital;
    }

    public function setMarital(?Marital $marital): void
    {
        $this->marital = $marital;
    }

    public function getMaritalId(): ?int
    {
        return $this->maritalId;
    }

    public function setMaritalId(?int $maritalId): void
    {
        $this->maritalId = $maritalId;
    }

    public function getBirthday(): ?Birthday
    {
        return $this->birthday;
    }

    public function setBirthday(?Birthday $birthday): void
    {
        if (null !== $birthday) {
            $birthday = new Birthday(date('Y-m-d', (int)$birthday->getValue()));
        }

        $this->birthday = $birthday;
    }

    public function getBirthdayPrivacy(): ?int
    {
        return $this->birthdayPrivacy;
    }

    public function setBirthdayPrivacy(?int $birthdayPrivacy): void
    {
        $this->birthdayPrivacy = $birthdayPrivacy;
    }

    public function getContactCountry(): ?Country
    {
        return $this->contactCountry;
    }

    public function setContactCountry(?Country $contactCountry): void
    {
        $this->contactCountry = $contactCountry;
    }

    public function getContactCountryName(): ?string
    {
        return $this->contactCountryName;
    }

    public function setContactCountryName(?string $contactCountryName): void
    {
        $this->contactCountryName = $contactCountryName;
    }

    public function getContactCity(): ?City
    {
        return $this->contactCity;
    }

    public function setContactCity(?City $contactCity): void
    {
        $this->contactCity = $contactCity;
    }

    public function getContactCityName(): ?string
    {
        return $this->contactCityName;
    }

    public function setContactCityName(?string $contactCityName): void
    {
        $this->contactCityName = $contactCityName;
    }

    public function getContactPhone(): ?Phone
    {
        return $this->contactPhone;
    }

    public function setContactPhone(?Phone $contactPhone): void
    {
        $this->contactPhone = $contactPhone;
    }

    public function getContactsEmail(): ?Email
    {
        return $this->contactsEmail;
    }

    public function setContactsEmail(?Email $contactsEmail): void
    {
        $this->contactsEmail = $contactsEmail;
    }

    public function getContactSite(): ?Site
    {
        return $this->contactSite;
    }

    public function setContactSite(?Site $contactSite): void
    {
        $this->contactSite = $contactSite;
    }

    public function getLatitude(): ?float
    {
        return $this->latitude;
    }

    public function setLatitude(?float $latitude): void
    {
        $this->latitude = $latitude;
    }

    public function getLongitude(): ?float
    {
        return $this->longitude;
    }

    public function setLongitude(?float $longitude): void
    {
        $this->longitude = $longitude;
    }

    public function getSpaceId(): ?int
    {
        return $this->spaceId;
    }

    public function setSpaceId(?int $spaceId): void
    {
        $this->spaceId = $spaceId;
    }

    public function getInterestsActivities(): ?string
    {
        return $this->interestsActivities;
    }

    public function setInterestsActivities(?string $interestsActivities): void
    {
        $this->interestsActivities = $interestsActivities;
    }

    public function getInterestsInterests(): ?string
    {
        return $this->interestsInterests;
    }

    public function setInterestsInterests(?string $interestsInterests): void
    {
        $this->interestsInterests = $interestsInterests;
    }

    public function getInterestsMusic(): ?string
    {
        return $this->interestsMusic;
    }

    public function setInterestsMusic(?string $interestsMusic): void
    {
        $this->interestsMusic = $interestsMusic;
    }

    public function getInterestsFilms(): ?string
    {
        return $this->interestsFilms;
    }

    public function setInterestsFilms(?string $interestsFilms): void
    {
        $this->interestsFilms = $interestsFilms;
    }

    public function getInterestsTv(): ?string
    {
        return $this->interestsTv;
    }

    public function setInterestsTv(?string $interestsTv): void
    {
        $this->interestsTv = $interestsTv;
    }

    public function getInterestsBooks(): ?string
    {
        return $this->interestsBooks;
    }

    public function setInterestsBooks(?string $interestsBooks): void
    {
        $this->interestsBooks = $interestsBooks;
    }

    public function getInterestsCitations(): ?string
    {
        return $this->interestsCitations;
    }

    public function setInterestsCitations(?string $interestsCitations): void
    {
        $this->interestsCitations = $interestsCitations;
    }

    public function getInterestsAbout(): ?string
    {
        return $this->interestsAbout;
    }

    public function setInterestsAbout(?string $interestsAbout): void
    {
        $this->interestsAbout = $interestsAbout;
    }

    public function getPositionPolitical(): ?string
    {
        return $this->positionPolitical;
    }

    public function setPositionPolitical(?string $positionPolitical): void
    {
        $this->positionPolitical = $positionPolitical;
    }

    public function getPositionReligion(): ?string
    {
        return $this->positionReligion;
    }

    public function setPositionReligion(?string $positionReligion): void
    {
        $this->positionReligion = $positionReligion;
    }

    public function getPositionLife(): ?string
    {
        return $this->positionLife;
    }

    public function setPositionLife(?string $positionLife): void
    {
        $this->positionLife = $positionLife;
    }

    public function getPositionPeople(): ?string
    {
        return $this->positionPeople;
    }

    public function setPositionPeople(?string $positionPeople): void
    {
        $this->positionPeople = $positionPeople;
    }

    public function getPositionSmoking(): ?string
    {
        return $this->positionSmoking;
    }

    public function setPositionSmoking(?string $positionSmoking): void
    {
        $this->positionSmoking = $positionSmoking;
    }

    public function getPositionAlcohol(): ?string
    {
        return $this->positionAlcohol;
    }

    public function setPositionAlcohol(?string $positionAlcohol): void
    {
        $this->positionAlcohol = $positionAlcohol;
    }

    public function getPositionInspiredBy(): ?string
    {
        return $this->positionInspiredBy;
    }

    public function setPositionInspiredBy(?string $positionInspiredBy): void
    {
        $this->positionInspiredBy = $positionInspiredBy;
    }

    public function getCountContacts(): int
    {
        return $this->countContacts;
    }

    public function setCountContacts(int $countContacts): void
    {
        $this->countContacts = $countContacts;
    }

    public function getCountSubscribers(): int
    {
        return $this->countSubscribers;
    }

    public function setCountSubscribers(int $countSubscribers): void
    {
        $this->countSubscribers = $countSubscribers;
    }

    public function getCountAudios(): int
    {
        return $this->countAudios;
    }

    public function setCountAudios(int $countAudios): void
    {
        $this->countAudios = $countAudios;
    }

    public function getCountPhotos(): int
    {
        return $this->countPhotos;
    }

    public function setCountPhotos(int $countPhotos): void
    {
        $this->countPhotos = $countPhotos;
    }

    public function getCountAlbums(): int
    {
        return $this->countAlbums;
    }

    public function setCountAlbums(int $countAlbums): void
    {
        $this->countAlbums = $countAlbums;
    }

    public function getCountPosts(): int
    {
        return $this->countPosts;
    }

    public function setCountPosts(int $countPosts): void
    {
        $this->countPosts = $countPosts;
    }

    public function getCountFlows(): int
    {
        return $this->countFlows;
    }

    public function setCountFlows(int $countFlows): void
    {
        $this->countFlows = $countFlows;
    }

    public function getCountFlowViews(): int
    {
        return $this->countFlowViews;
    }

    public function setCountFlowViews(int $countFlowViews): void
    {
        $this->countFlowViews = $countFlowViews;
    }

    public function getCountVideos(): int
    {
        return $this->countVideos;
    }

    public function setCountVideos(int $countVideos): void
    {
        $this->countVideos = $countVideos;
    }

    public function getCountCommunities(): int
    {
        return $this->countCommunities;
    }

    public function setCountCommunities(int $countCommunities): void
    {
        $this->countCommunities = $countCommunities;
    }

    public function getCountPlaces(): int
    {
        return $this->countPlaces;
    }

    public function setCountPlaces(int $countPlaces): void
    {
        $this->countPlaces = $countPlaces;
    }

    public function getCountEvents(): int
    {
        return $this->countEvents;
    }

    public function setCountEvents(int $countEvents): void
    {
        $this->countEvents = $countEvents;
    }

    public function getCountGifts(): int
    {
        return $this->countGifts;
    }

    public function setCountGifts(int $countGifts): void
    {
        $this->countGifts = $countGifts;
    }

    public function getCountStocks(): int
    {
        return $this->countStocks;
    }

    public function setCountStocks(int $countStocks): void
    {
        $this->countStocks = $countStocks;
    }

    public function getTimeReg(): ?int
    {
        return $this->timeReg;
    }

    public function setTimeReg(?int $timeReg): void
    {
        $this->timeReg = $timeReg;
    }

    public function getHideSections(): ?string
    {
        return $this->hideSections;
    }

    public function setHideSections(?string $hideSections): void
    {
        $this->hideSections = $hideSections;
    }

    public function isBot(): bool
    {
        return $this->isBot;
    }

    public function setIsBot(bool $isBot): void
    {
        $this->isBot = $isBot;
    }

    public function getMaritalStatus(): ?string
    {
        return $this->maritalStatus;
    }

    public function setMaritalStatus(?string $maritalStatus): void
    {
        $this->maritalStatus = $maritalStatus;
    }

    public function getRelatives(): ?string
    {
        return $this->relatives;
    }

    public function setRelatives(?string $relatives): void
    {
        $this->relatives = $relatives;
    }

    public function getPrivacy(): ?string
    {
        return $this->privacy;
    }

    public function setPrivacy(?string $privacy): void
    {
        $this->privacy = $privacy;
    }

    public function getUserSearchPref(): ?string
    {
        return $this->userSearchPref;
    }

    public function setUserSearchPref(?string $userSearchPref): void
    {
        $this->userSearchPref = $userSearchPref;
    }

    public function getUserCountryCityName(): ?string
    {
        return $this->userCountryCityName;
    }

    public function setUserCountryCityName(?string $userCountryCityName): void
    {
        $this->userCountryCityName = $userCountryCityName;
    }

    public function getYear(): ?int
    {
        return $this->year;
    }

    public function setYear(?int $year): void
    {
        $this->year = $year;
    }

    public function getMonth(): ?int
    {
        return $this->month;
    }

    public function setMonth(?int $month): void
    {
        $this->month = $month;
    }

    public function getDay(): ?int
    {
        return $this->day;
    }

    public function setDay(?int $day): void
    {
        $this->day = $day;
    }

    public function getOldPhoto(): ?string
    {
        return $this->oldPhoto;
    }

    public function setOldPhoto(?string $oldPhoto): void
    {
        $this->oldPhoto = $oldPhoto;
    }

    public function toArray(): array
    {
        return [
            'id' => $this->getId(),
            'screen_name' => $this->getScreenName()?->getValue(),
            'first_name' => $this->getFirstName()->getValue(),
            'last_name' => $this->getLastName()->getValue(),
            'first_name_translit' => $this->getFirstNameTranslit()?->getValue(),
            'last_name_translit' => $this->getLastNameTranslit()?->getValue(),
            'verified' => $this->getVerified()->getValue(),
            'deactivated' => $this->getDeactivated()->getValue(),
            'sex' => $this->getSex()?->getValue(),
            'photo' => $this->getPhoto()?->getValue(),
            'last_visit' => $this->getLastVisit(),
            'rate' => $this->getRate(),
            'rate_info' => $this->getRateInfo(),
            'country_id' => $this->getCountry()?->getId(),
            'country_name' => $this->getCountryName(),
            'city_id' => $this->getCity()?->getId(),
            'city_name' => $this->getCityName(),
            'birthday' => $this->getBirthday()?->getValue(),
        ];
    }
}
