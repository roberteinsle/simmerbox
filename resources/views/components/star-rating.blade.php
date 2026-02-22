@props(['recipeId', 'userRating' => 0, 'average' => null, 'count' => 0])

<div
    x-data="{
        rating: {{ (int) $userRating }},
        hover: 0,
        average: {{ $average ?? 'null' }},
        count: {{ (int) $count }},
        loading: false,
        async rate(stars) {
            if (this.rating === stars) { this.remove(); return; }
            this.loading = true;
            try {
                const res = await fetch('{{ route('recipes.rate', $recipeId) }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content,
                        'Accept': 'application/json',
                    },
                    body: JSON.stringify({ rating: stars })
                });
                const data = await res.json();
                this.rating = data.userRating;
                this.average = data.average;
                this.count = data.count;
            } finally {
                this.loading = false;
            }
        },
        async remove() {
            this.loading = true;
            try {
                const res = await fetch('{{ route('recipes.rate.destroy', $recipeId) }}', {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content,
                        'Accept': 'application/json',
                    },
                });
                const data = await res.json();
                this.rating = 0;
                this.average = data.average;
                this.count = data.count;
            } finally {
                this.loading = false;
            }
        }
    }"
    class="flex items-center gap-2"
>
    <div class="flex gap-0.5">
        <template x-for="star in [1,2,3,4,5]" :key="star">
            <button
                type="button"
                @click="rate(star)"
                @mouseenter="hover = star"
                @mouseleave="hover = 0"
                :disabled="loading"
                class="text-lg focus:outline-none disabled:opacity-50 transition cursor-pointer"
                :class="(hover ? star <= hover : star <= rating) ? 'text-yellow-400' : 'text-gray-300 dark:text-gray-600'"
            >&#9733;</button>
        </template>
    </div>
    <span class="text-sm text-gray-500 dark:text-gray-400" x-show="average !== null" x-cloak>
        <span x-text="average"></span> / 5
        (<span x-text="count"></span>)
    </span>
</div>
