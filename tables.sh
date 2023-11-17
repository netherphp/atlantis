#!/bin/bash

ndb sql-create Nether.User.EntitySession --drop -y
ndb sql-create Nether.User.EntityAccessType --drop -y

ndb sql-create Nether.Atlantis.Struct.EntityRelationship --drop -y
ndb sql-create Nether.Atlantis.Struct.EmailUpdate --drop -y
ndb sql-create Nether.Atlantis.Struct.LoginReset --drop -y
ndb sql-create Nether.Atlantis.Struct.ContactEntry --drop -y
ndb sql-create Nether.Atlantis.Struct.TrafficRow --drop -y
ndb sql-create Nether.Atlantis.Struct.TrafficReport --drop -y

ndb sql-create Nether.Atlantis.Media.File --drop -y
ndb sql-create Nether.Atlantis.Media.Tag --drop -y
ndb sql-create Nether.Atlantis.Media.TagLink --drop -y
ndb sql-create Nether.Atlantis.Media.VideoThirdParty

ndb sql-create Nether.Atlantis.Page.Entity --drop -y
ndb sql-create Nether.Atlantis.Page.Section --drop -y

ndb sql-create Nether.Atlantis.ShortURL.Entity --drop -y

ndb sql-create Nether.Atlantis.Profile.Entity
