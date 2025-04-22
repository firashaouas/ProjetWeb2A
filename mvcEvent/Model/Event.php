<?php
class Event {
    private  $id;
    private  $category;
    private  $name;
    private  $description;
    private  $price;
    private  $duration;
    private  $date;
    private  $location;
    private  $imageUrl;
    private  $totalSeats;
    private  $reservedSeats;

    // Constructor
    public function __construct(
         $category ,
         $name ,
         $description,
         $price ,
         $duration ,
         $date ,
         $location ,
         $imageUrl,
         $totalSeats ,
       $reservedSeats = 0
         ,
         
    ) {
        $this->category = $category;
        $this->name = $name;
        $this->description = $description;
        $this->price = $price;
        $this->duration = $duration;
        $this->date = $date ;
        $this->location = $location;
        $this->imageUrl = $imageUrl;
        $this->totalSeats = $totalSeats;
        $this->reservedSeats = $reservedSeats;
    }

    // Getters
    public function getId() {
        return $this->id;
    }

    public function getCategory() {
        return $this->category;
    }

    public function getName() {
        return $this->name;
    }

    public function getDescription() {
        return $this->description;
    }

    public function getPrice() {
        return $this->price;
    }

    public function getDuration() {
        return $this->duration;
    }

    public function getDate() {
        return $this->date;
    }

    public function getLocation() {
        return $this->location;
    }

    public function getImageUrl() {
        return $this->imageUrl;
    }

    public function getTotalSeats() {
        return $this->totalSeats;
    }

    public function getReservedSeats() {
        return $this->reservedSeats;
    }

    

    // Setters
    public function setId( $id) {
        $this->id = $id;
    }

    public function setCategory( $category) {
        $this->category = $category;
    }

    public function setName( $name) {
        $this->name = $name;
    }

    public function setDescription( $description) {
        $this->description = $description;
    }

    public function setPrice( $price){
        $this->price = $price;
    }

    public function setDuration( $duration) {
        $this->duration = $duration;
    }

    public function setDate( $date){
        $this->date = $date;
    }

    public function setLocation( $location){
        $this->location = $location;
    }

    public function setImageUrl( $imageUrl){
        $this->imageUrl = $imageUrl;
    }

    public function setTotalSeats( $totalSeats) {
        $this->totalSeats = $totalSeats;
    }

    public function setReservedSeats( $reservedSeats) {
        $this->reservedSeats = $reservedSeats;
    }

   
}