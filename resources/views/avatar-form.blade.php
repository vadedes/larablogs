<x-layout>
  <div class="container container--narrow py-md-5" >
    <h2 class="text-center mg-3">Upload a New Avatar</h2>
    {{-- when sending over an actual file instead of just typed values, enctype should be added to the form attributes --}}
    <form action="/manage-avatar" method="POST" enctype="multipart/form-data">
      @csrf
      <div class="mb-3">
        <input type="file" name="avatar" required>
        @error('avatar')
            <p class="alert small alert-danger shadow-sm">
              {{$message}}
            </p>
        @enderror
      </div>
      <button type="submit" class="btn btn-primary">Save</button>
    </form>
  </div>
</x-layout>