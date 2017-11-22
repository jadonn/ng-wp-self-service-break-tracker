<?php

class SelfServiceBreak implements MongoDB\BSON\Persistable
{
    private $id;
    private $username;
    private $active;
    private $status;
    private $hash;
    private $createdAt;

    public function __construct( $name ){
        $this->id = new MongoDB\BSON\ObjectID;
        $this->username = (string) $name;
        $this->active = true;
        $this->status = 'pending';
        $this->hash = bin2hex( random_bytes( 20 ) );
        $this->createdAt = new MongoDB\BSON\UTCDateTime;
        $this->updatedAt = $this->createdAt;
    }

    function bsonSerialize(){
        return [
            '_id'       => $this->id,
            'username'  => $this->username,
            'active'    => $this->active,
            'status'    => $this->status,
            'hash'      => $this->hash,
            'createdAt' => strval($this->createdAt),
            'updatedAt' => strval($this->updatedAt)
        ];
    }

    function bsonUnserialize( array $data ){
        $this->id        = $data[ '_id' ];
        $this->username  = $data[ 'username' ];
        $this->active    = $data[ 'active' ];
        $this->status    = $data[ 'status' ];
        $this->hash      = $data[ 'hash' ];
        $this->createdAt = $data[ 'createdAt' ];
        $this->updatedAt = $data[ 'updatedAt' ];
    }

}