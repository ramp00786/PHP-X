#include <stdio.h>
#include <time.h>

long current_time_ms() {
    struct timespec ts;
    clock_gettime(CLOCK_REALTIME, &ts);
    return (ts.tv_sec * 1000) + (ts.tv_nsec / 1000000);
}
