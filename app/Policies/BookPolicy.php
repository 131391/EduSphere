<?php

namespace App\Policies;

use App\Models\Book;
use App\Models\BookCategory;
use App\Models\BookIssue;
use App\Models\User;

class BookPolicy
{
    public function manage(User $user): bool
    {
        $currentSchool = app()->bound('currentSchool') ? app('currentSchool') : null;

        return $this->isLibraryOperator($user)
            && $currentSchool
            && $user->canAccessSchool($currentSchool->id);
    }

    public function update(User $user, Book $book): bool
    {
        return $this->isLibraryOperator($user) && $user->canAccessSchool($book->school_id);
    }

    public function delete(User $user, Book|BookCategory $resource): bool
    {
        return $this->isLibraryOperator($user) && $user->canAccessSchool($resource->school_id);
    }

    public function manageIssue(User $user, BookIssue $issue): bool
    {
        return $this->isLibraryOperator($user) && $user->canAccessSchool($issue->school_id);
    }

    private function isLibraryOperator(User $user): bool
    {
        return $user->isActive() && ($user->isSchoolAdmin() || $user->isLibrarian());
    }
}
